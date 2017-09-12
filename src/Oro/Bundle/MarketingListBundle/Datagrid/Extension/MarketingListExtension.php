<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;

use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Component\DoctrineUtils\ORM\HookUnionTrait;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * For segment based marketing lists show not only segment results but also already contacted entities.
 */
class MarketingListExtension extends AbstractExtension
{
    use HookUnionTrait;

    /** @deprecated since 1.10. Use config->getName() instead */
    const NAME_PATH = '[name]';

    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @var string[]
     */
    protected $appliedFor;

    /**
     * @var int
     */
    protected $marketingListId;

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

        if (!empty($this->appliedFor[$gridName])) {
            return false;
        }

        if (!$config->isOrmDatasource()) {
            return false;
        }

        $this->marketingListId = $this->marketingListHelper->getMarketingListIdByGridName($gridName);
        if (!$this->marketingListId) {
            return false;
        }

        $marketingList = $this->marketingListHelper->getMarketingList($this->marketingListId);

        if (!$marketingList || $marketingList->isManual()) {
            return false;
        }

        $definition = json_decode($marketingList->getDefinition(), true);

        // We should skip the configuration if it do not contain at least one filter
        if (empty($definition['filters'])) {
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

        $entityManager    = $qb->getEntityManager();
        $itemsQuery       = $this->createItemsQuery($entityManager);
        $uniqueIdentifier = QueryBuilderUtil::generateParameterName(HookUnionTrait::$walkerHookUnionKey);
        $walkerHook = " AND '$uniqueIdentifier' = '$uniqueIdentifier'";

        $configuration = $entityManager->getConfiguration();
        $configuration->setDefaultQueryHint(HookUnionTrait::$walkerHookUnionKey, $walkerHook);
        $configuration->setDefaultQueryHint(HookUnionTrait::$walkerHookUnionValue, $itemsQuery);

        $qb->resetDQLPart('where');

        /** @var string|Func $part */
        foreach ($parts as $part) {
            if (!is_string($part)) {
                $part = new Func($part->getName(), $part->getArguments()[0].$walkerHook);
            }

            $qb->andWhere($part);
        }

        $gridName = $config->getName();
        $this->appliedFor[$gridName] = true;
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
}
