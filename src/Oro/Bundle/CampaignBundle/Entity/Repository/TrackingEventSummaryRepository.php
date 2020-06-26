<?php

namespace Oro\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CampaignBundle\Entity\Campaign;

/**
 * Doctrine repository for TrackingEventSummary entity.
 */
class TrackingEventSummaryRepository extends EntityRepository
{
    /**
     * @param Campaign $campaign
     * @return array
     */
    public function getSummarizedStatistic(Campaign $campaign)
    {
        $today = new \DateTime('now', new \DateTimeZone('UTC'));

        $qb = $this->_em->createQueryBuilder()
            ->from('OroTrackingBundle:TrackingEvent', 'trackingEvent')
            ->join(
                'OroCampaignBundle:CampaignCodeHistory',
                'campaignCodeHistory',
                'WITH',
                'campaignCodeHistory.code = trackingEvent.code'
            )
            ->select(
                [
                    'trackingEvent.name',
                    'campaignCodeHistory.code',
                    'IDENTITY(trackingEvent.website) as websiteId',
                    'COUNT(trackingEvent.id) as visitCount',
                    'DATE(trackingEvent.loggedAt) as loggedAtDate',
                ]
            )
            ->andWhere('trackingEvent.website IS NOT NULL')
            ->andWhere('campaignCodeHistory.campaign = :campaign')
            ->andWhere('DATE(trackingEvent.loggedAt) < DATE(:today)')
            ->setParameter('campaign', $campaign)
            ->setParameter('today', $today, Types::DATETIME_MUTABLE)
            ->groupBy('trackingEvent.name, trackingEvent.website, campaignCodeHistory.code, loggedAtDate');

        if ($campaign->getReportRefreshDate()) {
            $qb->andWhere('DATE(trackingEvent.loggedAt) > DATE(:since)')
                ->setParameter('since', $campaign->getReportRefreshDate(), Types::DATETIME_MUTABLE);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
