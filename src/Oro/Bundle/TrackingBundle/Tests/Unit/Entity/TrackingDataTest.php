<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\TrackingBundle\Entity\TrackingData;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;

class TrackingDataTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingData */
    private $data;

    protected function setUp(): void
    {
        $this->data = new TrackingData();
    }

    public function testId()
    {
        $this->assertNull($this->data->getId());
    }

    public function testPrePersist()
    {
        $this->assertNull($this->data->getCreatedAt());
        $this->data->prePersist();
        $this->assertInstanceOf(\DateTime::class, $this->data->getCreatedAt());
    }

    /**
     * @dataProvider propertyProvider
     */
    public function testProperties(string $property, mixed $value, mixed $expected)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->assertNull(
            $propertyAccessor->getValue($this->data, $property)
        );

        $propertyAccessor->setValue($this->data, $property, $value);

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->data, $property)
        );
    }

    public function propertyProvider(): array
    {
        $date = new \DateTime();
        $event = new TrackingEvent();

        return [
            ['data', '{"test": "test"}', '{"test": "test"}'],
            ['event', $event, $event],
            ['createdAt', $date, $date],
        ];
    }
}
