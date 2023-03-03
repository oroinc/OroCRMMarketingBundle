<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class TrackingEventDictionaryTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingEventDictionary */
    private $trackingEvents;

    protected function setUp(): void
    {
        $this->trackingEvents = new TrackingEventDictionary();
    }

    public function testId()
    {
        $this->assertNull($this->trackingEvents->getId());
    }

    /**
     * @dataProvider propertyProvider
     */
    public function testProperties(string $property, mixed $value, mixed $expected)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertNull(
            $propertyAccessor->getValue($this->trackingEvents, $property)
        );

        $propertyAccessor->setValue($this->trackingEvents, $property, $value);

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->trackingEvents, $property)
        );
    }

    public function propertyProvider(): array
    {
        $website = new TrackingWebsite();

        return [
            ['name', 'visit', 'visit'],
            ['visitEvents', [], []],
            ['website', $website, $website]
        ];
    }
}
