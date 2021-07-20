<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\QueryDefinitionUtil;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;
use Oro\Component\DoctrineUtils\ORM\Walker\UnionOutputResultModifier;

/**
 * For segment based marketing lists show not only segment results but also already contacted entities.
 * Each oro_marketing_list_items_grid_1 grid has union to MarketingListItem
 */
class MarketingListExtension extends AbstractExtension
{
    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @var bool[]
     */
    protected $applicable = [];

    /**
     * @var int
     */
    protected $marketingListId;

    public function __construct(MarketingListHelper $marketingListHelper)
    {
        $this->marketingListHelper = $marketingListHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        if (!parent::isApplicable($config)) {
            return false;
        }

        $cacheKey = $this->getCacheKey($config);
        if (array_key_exists($cacheKey, $this->applicable)) {
            return $this->applicable[$cacheKey];
        }

        if (!$config->isOrmDatasource()) {
            $this->applicable[$cacheKey] = false;

            return false;
        }

        $this->marketingListId = $this->marketingListHelper->getMarketingListIdByGridName($config->getName());
        if (!$this->marketingListId) {
            $this->applicable[$cacheKey] = false;

            return false;
        }

        $marketingList = $this->marketingListHelper->getMarketingList($this->marketingListId);

        // Accept only segment based marketing lists
        return $this->isMarketingListApplicable($marketingList, $config, $cacheKey);
    }

    private function isMarketingListApplicable(
        ?MarketingList $marketingList,
        DatagridConfiguration $config,
        string $cacheKey
    ): bool {
        if (!$marketingList || $marketingList->isManual()) {
            $this->applicable[$cacheKey] = false;

            return false;
        }

        if (empty($config['options']['add_contacted_items']) && !$marketingList->isUnion()) {
            $this->applicable[$cacheKey] = false;

            return false;
        }

        $definition = QueryDefinitionUtil::decodeDefinition($marketingList->getDefinition());

        // We should skip the configuration if it do not contain at least one filter
        if (empty($definition['filters'])) {
            $this->applicable[$cacheKey] = false;

            return false;
        }

        return true;
    }

    /**
     * @param OrmDatasource $datasource
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        if (!$this->isApplicable($config)) {
            return;
        }

        /** @var QueryBuilder $qb */
        $qb = $datasource->getQueryBuilder();
        $dqlParts = $qb->getDQLParts();

        if (empty($dqlParts['where'])) {
            return;
        }

        /** @var Andx $conditions */
        $conditions = $dqlParts['where'];

        $parts = $conditions->getParts();
        if (empty($parts)) {
            return;
        }

        $entityManager = $qb->getEntityManager();
        $itemsQuery = $this->createItemsQuery($entityManager);
        $uniqueIdentifier = QueryBuilderUtil::generateParameterName(UnionOutputResultModifier::HINT_UNION_KEY);
        $walkerHook = " AND '$uniqueIdentifier' = '$uniqueIdentifier'";

        $configuration = $entityManager->getConfiguration();
        $configuration->setDefaultQueryHint(UnionOutputResultModifier::HINT_UNION_KEY, $walkerHook);
        $configuration->setDefaultQueryHint(UnionOutputResultModifier::HINT_UNION_VALUE, $itemsQuery);

        $qb->resetDQLPart('where');

        /** @var string|Func $part */
        foreach ($parts as $part) {
            if (!is_string($part)) {
                $part = new Func($part->getName(), $part->getArguments()[0] . $walkerHook);
            }

            $qb->andWhere($part);
        }

        $cacheKey = $this->getCacheKey($config);
        $this->applicable[$cacheKey] = false;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return -10;
    }

    /**
     * @param EntityManagerInterface $entityManager
     *
     * @return string
     */
    protected function createItemsQuery(EntityManagerInterface $entityManager)
    {
        $itemsQb = $entityManager->createQueryBuilder();
        $itemsQb
            ->select('item.entityId')
            ->from('OroMarketingListBundle:MarketingListItem', 'item')
            ->where($itemsQb->expr()->eq('item.marketingList', $this->marketingListId));

        return $itemsQb->getQuery()->getSQL();
    }

    private function getCacheKey(DatagridConfiguration $config): string
    {
        return md5(json_encode($config->toArray()));
    }
}
