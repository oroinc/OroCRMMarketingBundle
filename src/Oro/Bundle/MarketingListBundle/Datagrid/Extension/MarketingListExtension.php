<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid\Extension;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;

/**
 * For segment based marketing lists show not only segment results but also already contacted entities.
 */
class MarketingListExtension extends AbstractExtension
{
    /** @deprecated since 1.10. Use config->getName() instead */
    const NAME_PATH = '[name]';

    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @var bool[]
     */
    protected $applicable = [];

    /**
     * @param MarketingListHelper $marketingListHelper
     */
    public function __construct(MarketingListHelper $marketingListHelper)
    {
        $this->marketingListHelper = $marketingListHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $gridName = $config->getName();

        $cacheKey = $this->getCacheKey($config);
        if (array_key_exists($cacheKey, $this->applicable)) {
            return $this->applicable[$cacheKey];
        }

        if (!$config->isOrmDatasource()) {
            $this->applicable[$cacheKey] = false;
            return false;
        }

        $marketingListId = $this->marketingListHelper->getMarketingListIdByGridName($gridName);
        if (!$marketingListId) {
            $this->applicable[$cacheKey] = false;
            return false;
        }

        $marketingList = $this->marketingListHelper->getMarketingList($marketingListId);

        if (!$marketingList || $marketingList->isManual()) {
            $this->applicable[$cacheKey] = false;
            return false;
        }

        $definition = json_decode($marketingList->getDefinition(), true);

        // We should skip the configuration if it do not contain at least one filter
        if (empty($definition['filters'])) {
            $this->applicable[$cacheKey] = false;
            return false;
        }

        // Accept only segment based marketing lists
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        if (!$this->isApplicable($config)) {
            return;
        }

        /** @var OrmDatasource $datasource */
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

        $qb->resetDQLPart('where');

        $addParameter = false;
        foreach ($parts as $part) {
            if (!is_string($part)) {
                $part = $qb->expr()->orX(
                    $part,
                    $this->createItemsExpr($qb)
                );

                $addParameter = true;
            }

            $qb->andWhere($part);
        }

        $gridName = $config->getName();

        if ($addParameter) {
            $qb->setParameter(
                'marketingListId',
                $this->marketingListHelper->getMarketingListIdByGridName($gridName)
            );
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
     * @param QueryBuilder $qb
     *
     * @return mixed
     */
    protected function createItemsExpr(QueryBuilder $qb)
    {
        $itemsQb = clone $qb;
        $itemsQb->resetDQLParts();

        $itemsQb
            ->select('item.entityId')
            ->from('OroMarketingListBundle:MarketingListItem', 'item')
            ->andWhere('item.marketingList = :marketingListId');

        return $itemsQb->expr()->in($qb->getRootAliases()[0], $itemsQb->getDQL());
    }

    /**
     * @param DatagridConfiguration $config
     * @return string
     */
    private function getCacheKey(DatagridConfiguration $config): string
    {
        return md5(json_encode($config->toArray()));
    }
}
