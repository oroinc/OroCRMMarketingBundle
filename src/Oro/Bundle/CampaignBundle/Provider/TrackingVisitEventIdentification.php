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

    /**
     * {@inheritDoc}
     */
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function identify(TrackingVisit $trackingVisit)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityTarget()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventTargets()
    {
        return [Campaign::class];
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        return null !== $trackingVisitEvent->getWebEvent()->getCode();
    }

    /**
     * {@inheritDoc}
     */
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $campaign = $this->doctrine->getRepository(Campaign::class)
            ->findOneByCode($trackingVisitEvent->getWebEvent()->getCode());

        return null !== $campaign ? [$campaign] : [];
    }
}
