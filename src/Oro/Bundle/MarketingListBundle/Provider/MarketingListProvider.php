<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\QueryDesignerBundle\Grid\QueryDesignerQueryConfiguration;
use Oro\Bundle\TagBundle\Grid\TagsExtension;

/**
 * Return Marketing list entities iterator or QB. Both contain actual ML state and previously contacted entities.
 * Use \Oro\Bundle\MarketingListBundle\Entity\MarketingList::$union to remove MLRI\MLUI for runtime when you pass
 * MarketingList to this provider
 */
class MarketingListProvider
{
    // entity.id + contactedTimes + lastContactedAt + MLI + MLRI is null + MLUI
    const RESULT_ITEMS_MIXIN = 'oro-marketing-list-items-mixin';
    const MANUAL_RESULT_ITEMS_MIXIN = 'oro-marketing-list-manual-items-mixin';

    // entity.id + MLI
    const FULL_ENTITIES_MIXIN = 'oro-marketing-full-mixin';

    // entity.id + MLI + MLRI is null + MLUI is null
    const RESULT_ENTITIES_MIXIN = 'oro-marketing-list-entities-mixin';
    const MANUAL_RESULT_ENTITIES_MIXIN = 'oro-marketing-list-manual-entities-mixin';

    /**
     * @var Manager
     */
    protected $dataGridManager;

    /**
     * @var array
     */
    protected $dataGrid = [];

    /**
     * @var array
     */
    protected $columnInformation = [];

    public function __construct(Manager $dataGridManager)
    {
        $this->dataGridManager = $dataGridManager;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $mixin
     *
     * @return QueryBuilder
     */
    public function getMarketingListQueryBuilder(MarketingList $marketingList, $mixin = null)
    {
        $dataGrid = $this->getMarketingListDataGrid($marketingList, $mixin);

        /** @var OrmDatasource $dataSource */
        $dataSource = $dataGrid->getDatasource();
        $qb =  $dataSource->getQueryBuilder();
        $this->saveColumnInformation($marketingList, $dataGrid, $qb);

        return $qb;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $mixin
     *
     * @return \Iterator
     */
    public function getMarketingListResultIterator(MarketingList $marketingList, $mixin = null)
    {
        if (!$mixin) {
            if ($marketingList->isManual()) {
                $mixin = self::MANUAL_RESULT_ITEMS_MIXIN;
            } else {
                $mixin = self::RESULT_ITEMS_MIXIN;
            }
        }

        $queryBuilder = $this->getMarketingListQueryBuilder($marketingList, $mixin);
        $dataGridConfig = $this->getMarketingListDataGrid($marketingList, $mixin)->getConfig();

        $skipCountWalker = $dataGridConfig->offsetGetByPath(
            DatagridConfiguration::DATASOURCE_SKIP_COUNT_WALKER_PATH,
            false
        );
        $iterator = new BufferedIdentityQueryResultIterator($queryBuilder, !$skipCountWalker);

        return $iterator;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $mixin
     *
     * @return QueryBuilder
     */
    public function getMarketingListEntitiesQueryBuilder(MarketingList $marketingList, $mixin = null)
    {
        if (!$mixin) {
            if ($marketingList->isManual()) {
                $mixin = self::MANUAL_RESULT_ENTITIES_MIXIN;
            } else {
                $mixin = self::RESULT_ENTITIES_MIXIN;
            }
        }

        $queryBuilder = clone $this->getMarketingListQueryBuilder($marketingList, $mixin);

        /** @var From[] $from */
        $from = $queryBuilder->getDQLPart('from');
        $entityAlias = $from[0]->getAlias();

        // Select only entity related information ordered by identifier field for maximum performance
        $queryBuilder
            ->resetDQLPart('select')
            ->resetDQLPart('orderBy')
            ->select($entityAlias)
            ->orderBy($entityAlias . '.id');

        return $queryBuilder;
    }

    /**
     * @param MarketingList $marketingList
     * @param string|null $mixin
     * @return \Iterator
     */
    public function getEntitiesIterator(MarketingList $marketingList, $mixin = null)
    {
        $queryBuilder = $this->getMarketingListEntitiesQueryBuilder($marketingList, $mixin);
        $columnInformation = $this->getColumnInformation($marketingList);
        foreach ($columnInformation as $alias => $expr) {
            $queryBuilder->addSelect(sprintf('%s as %s', $expr, $alias));
        }

        return new BufferedIdentityQueryResultIterator($queryBuilder, false);
    }

    /**
     * @param MarketingList $marketingList
     * @return null|array
     */
    public function getColumnInformation(MarketingList $marketingList)
    {
        if (array_key_exists($marketingList->getId(), $this->columnInformation)) {
            return $this->columnInformation[$marketingList->getId()];
        }

        return null;
    }

    /**
     * @param MarketingList $marketingList
     * @param null|string $mixin
     *
     * @return DatagridInterface
     */
    protected function getMarketingListDataGrid(MarketingList $marketingList, $mixin = null)
    {
        $dataGridName = ConfigurationProvider::GRID_PREFIX . $marketingList->getId();

        $resultKey = $dataGridName . $mixin;
        if (empty($this->dataGrid[$resultKey])) {
            $gridParameters = [
                PagerInterface::PAGER_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true],
                /**
                 * Disable tags extension because of problems with AuthorizationCheckerInterface::isGranted
                 * calls during console command running
                 */
                TagsExtension::TAGS_ROOT_PARAM => [TagsExtension::DISABLED_PARAM => true]
            ];
            if ($mixin) {
                $gridParameters['grid-mixin'] = $mixin;
            }
            $datagrid = $this->dataGridManager->getDatagrid($dataGridName, $gridParameters);
            $datagrid->acceptDatasource();
            $this->dataGrid[$resultKey] = $datagrid;
        }

        return $this->dataGrid[$resultKey];
    }

    protected function saveColumnInformation(
        MarketingList $marketingList,
        DatagridInterface $dataGrid,
        QueryBuilder $qb
    ) {
        /** @var Select[] $selects */
        $selects = $qb->getDQLPart('select');
        $columnToSelectExpr = [];
        foreach ($selects as $select) {
            foreach ($select->getParts() as $selectPart) {
                $selectData = explode(strrev(' as '), strrev($selectPart), 2);
                if (count($selectData) === 2) {
                    $columnToSelectExpr[strrev($selectData[0])] = strrev($selectData[1]);
                }
            }
        }

        $columnAliases = $dataGrid->getConfig()->offsetGetByPath(QueryDesignerQueryConfiguration::COLUMN_ALIASES, []);
        $columnInformation = [];
        foreach ($columnAliases as $alias => $selectAlias) {
            if (array_key_exists($selectAlias, $columnToSelectExpr)) {
                $columnInformation[$alias] = $columnToSelectExpr[$selectAlias];
            }
        }

        $this->columnInformation[$marketingList->getId()] = $columnInformation;
    }
}
