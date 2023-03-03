<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\TrackingBundle\Entity\TrackingData;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class TrackingEventTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingEvent */
    private $event;

    protected function setUp(): void
    {
        $this->event = new TrackingEvent();
    }

    public function testId()
    {
        $this->assertNull($this->event->getId());
    }

    public function testPrePersist()
    {
        $this->assertNull($this->event->getCreatedAt());
        $this->event->prePersist();
        $this->assertInstanceOf(\DateTime::class, $this->event->getCreatedAt());
    }

    /**
     * @dataProvider propertyProvider
     */
    public function testProperties(string $property, mixed $value, mixed $expected, bool $isBool = false)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($isBool) {
            $this->assertFalse(
                $propertyAccessor->getValue($this->event, $property)
            );
        } else {
            $this->assertNull(
                $propertyAccessor->getValue($this->event, $property)
            );
        }

        $propertyAccessor->setValue($this->event, $property, $value);

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->event, $property)
        );
    }

    public function propertyProvider(): array
    {
        $website = new TrackingWebsite();
        $eventData = new TrackingData();
        $date = new \DateTime();

        return [
            ['name', 'name', 'name'],
            ['value', 1, 1],
            ['userIdentifier', 'userIdentifier', 'userIdentifier'],
            ['url', 'url', 'url'],
            ['title', 'title', 'title'],
            ['code', 'code', 'code'],
            ['website', $website, $website],
            ['createdAt', $date, $date],
            ['loggedAt', $date, $date],
            ['eventData', $eventData, $eventData],
            ['parsed', 1, 1, true]
        ];
    }
}
