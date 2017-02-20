<?php

namespace Oro\Bundle\CampaignBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCode;

class CampaignCodeListener
{
    /**
     * Before flush, create new campaign code
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Campaign) {
                $this->createCampaignCode($entity, $em);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Campaign && array_key_exists('code', $uow->getEntityChangeSet($entity))) {
                $this->createCampaignCode($entity, $em);
            }
        }
    }

    /**
     * Create new campaign code
     *
     * @param Campaign $campaign
     * @param EntityManager $em
     */
    protected function createCampaignCode(Campaign $campaign, EntityManager $em)
    {
        $code = $em->getRepository('OroCampaignBundle:CampaignCode')->findOneBy(['code' => $campaign->getCode()]);
        if (!$code) {
            $code = new CampaignCode();
            $code->setCampaign($campaign);
            $code->setCode($campaign->getCode());

            $em->persist($code);
            $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(CampaignCode::class), $code);
        }
    }
}
