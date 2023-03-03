<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class TrackingVisitEventTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingVisitEvent */
    private $trackingVisitEvent;

    protected function setUp(): void
    {
        $this->trackingVisitEvent = new TrackingVisitEvent();
    }

    public function testId()
    {
        $this->assertNull($this->trackingVisitEvent->getId());
    }

    public function testParsingCount()
    {
        $this->assertEquals(0, $this->trackingVisitEvent->getParsingCount());

        $this->trackingVisitEvent->setParsingCount(1);
        $this->assertEquals(1, $this->trackingVisitEvent->getParsingCount());
    }

    /**
     * @dataProvider propertyProvider
     */
    public function testProperties(string $property, mixed $value, mixed $expected)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertNull(
            $propertyAccessor->getValue($this->trackingVisitEvent, $property)
        );

        $propertyAccessor->setValue($this->trackingVisitEvent, $property, $value);

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->trackingVisitEvent, $property)
        );
    }

    public function propertyProvider(): array
    {
        $visit = new TrackingVisit();
        $event = new TrackingEventDictionary();
        $webEvent = new TrackingEvent();
        $website = new TrackingWebsite();

        return [
            ['visit', $visit, $visit],
            ['event', $event, $event],
            ['webEvent', $webEvent, $webEvent],
            ['website', $website, $website]
        ];
    }
}
