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
}
