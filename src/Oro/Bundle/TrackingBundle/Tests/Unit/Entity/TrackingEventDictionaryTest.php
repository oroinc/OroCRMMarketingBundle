<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Symfony\Component\PropertyAccess\PropertyAccess;

class TrackingEventDictionaryTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingEventDictionary */
    protected $trackingEvents;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->trackingEvents = new TrackingEventDictionary();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset($this->trackingEvents);
    }

    public function testId()
    {
        $this->assertNull($this->trackingEvents->getId());
    }

    /**
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     *
     * @dataProvider propertyProvider
     */
    public function testProperties($property, $value, $expected)
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

    /**
     * @return array
     */
    public function propertyProvider()
    {
        $website = new TrackingWebsite();

        return [
            ['name', 'visit', 'visit'],
            ['visitEvents', [], []],
            ['website', $website, $website]
        ];
    }
}
