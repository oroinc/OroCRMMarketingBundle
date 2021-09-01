<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Provider;

use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentificationProvider;
use Oro\Bundle\TrackingBundle\Tests\Unit\Fixture\TestProvider;

class TrackingEventIdentificationProviderTest extends \PHPUnit\Framework\TestCase
{
    private TrackingEventIdentificationProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new TrackingEventIdentificationProvider([new TestProvider()]);
    }

    public function testIdentify(): void
    {
        self::assertEquals(
            'identity',
            $this->provider->identify(new TrackingVisit())->value
        );
    }

    public function testGetTargetIdentityEntities(): void
    {
        self::assertEquals(
            ['\stdClassIdentity'],
            $this->provider->getTargetIdentityEntities()
        );
    }

    public function testGetEventTargetEntities(): void
    {
        self::assertEquals(
            ['\stdClass'],
            $this->provider->getEventTargetEntities()
        );
    }

    public function testProcessEvent(): void
    {
        self::assertEquals(
            'event',
            $this->provider->processEvent(new TrackingVisitEvent())[0]->value
        );
    }
}
