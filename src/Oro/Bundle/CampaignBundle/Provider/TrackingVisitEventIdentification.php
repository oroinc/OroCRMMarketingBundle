<?php

namespace Oro\Bundle\CampaignBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentifierInterface;

/**
 * Checks if given tracking event is supported by identifier.
 */
class TrackingVisitEventIdentification implements TrackingEventIdentifierInterface
{
    protected ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[\Override]
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        return false;
    }

    #[\Override]
    public function identify(TrackingVisit $trackingVisit)
    {
    }

    #[\Override]
    public function getIdentityTarget()
    {
        return null;
    }

    #[\Override]
    public function getEventTargets()
    {
        return [Campaign::class];
    }

    #[\Override]
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        return null !== $trackingVisitEvent->getWebEvent()->getCode();
    }

    #[\Override]
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $campaign = $this->doctrine->getRepository(Campaign::class)
            ->findOneByCode($trackingVisitEvent->getWebEvent()->getCode());

        return null !== $campaign ? [$campaign] : [];
    }
}
