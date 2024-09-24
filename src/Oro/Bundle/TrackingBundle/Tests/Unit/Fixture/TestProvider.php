<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Fixture;

use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentifierInterface;

class TestProvider implements TrackingEventIdentifierInterface
{
    #[\Override]
    public function isApplicable(TrackingVisit $trackingVisit)
    {
        return true;
    }

    #[\Override]
    public function identify(TrackingVisit $trackingVisit)
    {
        $result = new \stdClass();
        $result->value = 'identity';

        return $result;
    }

    #[\Override]
    public function getIdentityTarget()
    {
        return '\stdClassIdentity';
    }

    #[\Override]
    public function isApplicableVisitEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        return true;
    }

    #[\Override]
    public function processEvent(TrackingVisitEvent $trackingVisitEvent)
    {
        $result = new \stdClass();
        $result->value = 'event';

        return [$result];
    }

    #[\Override]
    public function getEventTargets()
    {
        return ['\stdClass'];
    }
}
