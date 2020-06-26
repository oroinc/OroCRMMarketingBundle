<?php

namespace Oro\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;

/**
 * ORM entity repository for EmailCampaign entity.
 */
class EmailCampaignRepository extends EntityRepository
{
    /**
     * @return EmailCampaign[]
     */
    public function findEmailCampaignsToSend()
    {
        $qb = $this->prepareEmailCampaignsToSendQuery();
        $qb->select('email_campaign');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int
     */
    public function countEmailCampaignsToSend()
    {
        $qb = $this->prepareEmailCampaignsToSendQuery();
        $qb->select('COUNT(email_campaign.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return QueryBuilder
     */
    protected function prepareEmailCampaignsToSendQuery()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb ->from('OroCampaignBundle:EmailCampaign', 'email_campaign')
            ->where($qb->expr()->eq('email_campaign.sent', ':sent'))
            ->andWhere($qb->expr()->eq('email_campaign.schedule', ':scheduleType'))
            ->andWhere($qb->expr()->isNotNull('email_campaign.scheduledFor'))
            ->andWhere($qb->expr()->lte('email_campaign.scheduledFor', ':currentTimestamp'))
            ->setParameter('sent', false, Types::BOOLEAN)
            ->setParameter('scheduleType', EmailCampaign::SCHEDULE_DEFERRED, Types::STRING)
            ->setParameter('currentTimestamp', new \DateTime('now', new \DateTimeZone('UTC')), Types::DATETIME_MUTABLE);

        return $qb;
    }
}
