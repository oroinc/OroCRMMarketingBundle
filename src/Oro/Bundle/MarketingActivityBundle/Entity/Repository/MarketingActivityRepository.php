<?php

namespace Oro\Bundle\MarketingActivityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class MarketingActivityRepository extends EntityRepository
{
    /**
     * @param integer $campaignId
     * @param string  $entityClass
     * @param integer $entityId
     *
     * @return QueryBuilder
     */
    public function getMarketingActivitySummaryQueryBuilder($campaignId, $entityClass, $entityId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('COUNT(ma.id) as value, IDENTITY(ma.type) as typeId')
            ->from('OroMarketingActivityBundle:MarketingActivity', 'ma')
            ->where('ma.campaign = :campaignId')
            ->groupBy('typeId')
            ->setParameter(':campaignId', $campaignId);

        if (!empty($entityClass) && !empty($entityId)) {
            $queryBuilder->andWhere('ma.entityClass = :entityClass')
                ->andWhere('ma.entityId = :entityId')
                ->setParameter(':entityClass', $entityClass)
                ->setParameter(':entityId', $entityId);
        }

        return $queryBuilder;
    }

    /**
     * @param integer $campaignId
     * @param string  $entityClass
     * @param integer $entityId
     *
     * @return array
     */
    public function getMarketingActivitySummaryByCampaign($campaignId, $entityClass, $entityId)
    {
        $summary = $this->getMarketingActivitySummaryQueryBuilder($campaignId, $entityClass, $entityId)
            ->getQuery()
            ->getResult();

        $result = [];

        foreach ($summary as $item) {
            $result[$item['typeId']] = $item['value'];
        }

        return $result;
    }

    /**
     * @param integer $campaignId
     *
     * @return boolean
     */
    public function getMarketingActivitySummaryCountByCampaign($campaignId)
    {
        return (bool) $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(ma.id)')
            ->from('OroMarketingActivityBundle:MarketingActivity', 'ma')
            ->where('ma.campaign = :campaignId')
            ->setParameter(':campaignId', $campaignId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $entityClass
     * @param int    $entityId
     * @param array  $pageFilter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMarketingActivitySectionItemsQueryBuilder($entityClass, $entityId, $pageFilter = null)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('IDENTITY(ma.campaign) as id, campaign.name as campaignName')
            ->addSelect('MAX(ma.actionDate) as eventDate, campaign.updatedAt, campaign.createdAt')
            ->from('OroMarketingActivityBundle:MarketingActivity', 'ma')
            ->join('ma.campaign', 'campaign')
            ->where('ma.entityClass = :entityClass')
            ->andWhere('ma.entityId = :entityId')
            ->groupBy('ma.campaign, campaign.name, campaign.updatedAt, campaign.createdAt')
            ->orderBy('eventDate', 'DESC')
            ->setParameter(':entityClass', $entityClass)
            ->setParameter(':entityId', $entityId);

        if (!empty($pageFilter['date']) && !empty($pageFilter['ids'])) {
            $this->applyPageFilter($queryBuilder, $pageFilter);
        }

        return $queryBuilder;
    }

    /**
     * @param array   $items
     * @param string  $entityClass
     * @param integer $entityId
     *
     * @return MarketingActivityRepository
     */
    public function addEventTypeData(&$items, $entityClass, $entityId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('ma.actionDate, IDENTITY(ma.campaign) as campaignId, type.name')
            ->from('OroMarketingActivityBundle:MarketingActivity', 'ma')
            ->join('ma.type', 'type')
            ->where('ma.entityClass = :entityClass')
            ->andWhere('ma.entityId = :entityId')
            ->setParameter(':entityClass', $entityClass)
            ->setParameter(':entityId', $entityId);

        /** @var Orx $orX */
        $orX = $queryBuilder->expr()->orX();
        $i = 0;
        foreach ($items as $item) {
            $i++;
            $parameterName = 'campaignId' . $i;
            $orX->add($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('ma.campaign', ':' . $parameterName),
                $queryBuilder->expr()->eq('ma.actionDate', $queryBuilder->expr()->literal($item['eventDate']))
            ));
            $queryBuilder->setParameter($parameterName, $item['id']);
        }
        $queryBuilder->andWhere($orX);

        $types = $queryBuilder->getQuery()->getArrayResult();

        foreach ($items as &$item) {
            $item['eventType'] = '';
            foreach ($types as $type) {
                if ($item['id'] == $type['campaignId']
                    && $item['eventDate'] == $type['actionDate']->format('Y-m-d H:i:s')
                ) {
                    $item['eventType'] = $type['name'];
                }
            }
        }

        return $this;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $pageFilter
     *
     * @return MarketingActivityRepository
     */
    protected function applyPageFilter(QueryBuilder $queryBuilder, $pageFilter)
    {
        $dateFilter = new \DateTime($pageFilter['date'], new \DateTimeZone('UTC'));
        $orderDirection = $pageFilter['action'] === 'prev' ? 'ASC' : 'DESC';

        $queryBuilder->andWhere($queryBuilder->expr()->notIn('campaign.id', ':ids'));
        $queryBuilder->setParameter('ids', $pageFilter['ids']);
        if ($pageFilter['action'] === 'prev') {
            $queryBuilder->having($queryBuilder->expr()->gte('eventDate', ':dateFilter'));
        } else {
            $queryBuilder->having($queryBuilder->expr()->lte('eventDate', ':dateFilter'));
        }
        $queryBuilder->setParameter(':dateFilter', $dateFilter->format('Y-m-d H:i:s'));
        $queryBuilder->orderBy('eventDate', $orderDirection);

        return $this;
    }
}
