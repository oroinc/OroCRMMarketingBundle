<?php

namespace Oro\Bundle\CampaignBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;

class CampaignCodeHistoryListener
{
    /**
     * Before flush, create new campaign code
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Campaign) {
                $this->createCampaignCodeHistory($entity, $em);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Campaign && array_key_exists('code', $uow->getEntityChangeSet($entity))) {
                $this->createCampaignCodeHistory($entity, $em);
            }
        }
    }

    /**
     * Create new campaign code
     */
    protected function createCampaignCodeHistory(Campaign $campaign, EntityManager $em)
    {
        $codeHistory = $em->getRepository('OroCampaignBundle:CampaignCodeHistory')
            ->findOneBy(['code' => $campaign->getCode()]);
        if (!$codeHistory) {
            $codeHistory = new CampaignCodeHistory();
            $codeHistory->setCampaign($campaign);
            $codeHistory->setCode($campaign->getCode());

            $em->persist($codeHistory);
            $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(CampaignCodeHistory::class), $codeHistory);
        }
    }
}
