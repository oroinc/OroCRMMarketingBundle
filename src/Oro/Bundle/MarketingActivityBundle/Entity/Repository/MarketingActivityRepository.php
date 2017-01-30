<?php

namespace Oro\Bundle\MarketingActivityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class MarketingActivityRepository
 * @package Oro\Bundle\MarketingActivityBundle\Entity\Repository
 */
class MarketingActivityRepository extends EntityRepository
{
    /**
     * @param $campaignId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMarketingActivitySummaryQueryBuilder($campaignId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('count(ma.id) as value, type.id as typeId')
            ->from('OroMarketingActivityBundle:MarketingActivity', 'ma')
            ->join('ma.type', 'type')
            ->where('ma.campaign = :campaignId')
            ->groupBy('type.id')
            ->setParameter(':campaignId', $campaignId);

        return $queryBuilder;
    }

    /**
     * @param $campaignId
     *
     * @return array
     */
    public function getMarketingActivitySummaryByCampaign($campaignId)
    {
        $summary = $this->getMarketingActivitySummaryQueryBuilder($campaignId)
            ->getQuery()
            ->getResult();

        $result = [];

        foreach ($summary as $item) {
            $result[$item['typeId']] = $item['value'];
        }

        return $result;
    }

    /**
     * @param $entityClass
     * @param $entityId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMarketingActivitySectionItemsQueryBuilder($entityClass, $entityId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('campaign.id as id, campaign.name as campaignName, type.name as eventType')
            ->addSelect('MAX(ma.actionDate) as eventDate, campaign.updatedAt, campaign.createdAt')
            ->from('OroMarketingActivityBundle:MarketingActivity', 'ma')
            ->join('ma.type', 'type')
            ->join('ma.campaign', 'campaign')
            ->where('ma.entityClass = :entityClass')
            ->andWhere('ma.entityId = :entityId')
            ->groupBy('ma.campaign')
            ->orderBy('eventDate', 'DESC')
            ->setParameter(':entityClass', $entityClass)
            ->setParameter(':entityId', $entityId);

        return $queryBuilder;
    }
}
