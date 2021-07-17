<?php

namespace Oro\Bundle\CampaignBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentifierInterface;

/**
 * Checks if given tracking event is supported by identifier
 */
class TrackingVisitEventIdentification implements TrackingEventIdentifierInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function identify(TrackingVisit $trackingVisit)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityTarget()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventTargets()
    {
        return [
            'Oro\Bundle\CampaignBundle\Entity\Campaign'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $code = $trackingVisitEvent->getWebEvent()->getCode();
        return !is_null($code);
    }

    /**
     * {@inheritdoc}
     */
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $code = $trackingVisitEvent->getWebEvent()->getCode();
        $campaign = $this->registry->getManagerForClass(Campaign::class)
            ->getRepository(Campaign::class)
            ->findOneByCode($code);

        if ($campaign) {
            return [$campaign];
        }

        return [];
    }
}
