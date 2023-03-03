<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class TrackingVisitTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrackingVisit */
    private $trackingVisit;

    protected function setUp(): void
    {
        $this->trackingVisit = new TrackingVisit();
    }

    public function testId()
    {
        $this->assertNull($this->trackingVisit->getId());
    }

    /**
     * @dataProvider propertyProvider
     */
    public function testProperties(string $property, mixed $value, mixed $expected, bool $isBool = false)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($isBool) {
            $this->assertFalse(
                $propertyAccessor->getValue($this->trackingVisit, $property)
            );
        } elseif (!in_array($property, ['parsedUid', 'parsingCount'], true)) {
            $this->assertNull(
                $propertyAccessor->getValue($this->trackingVisit, $property)
            );
        } else {
            $this->assertEquals(
                0,
                $propertyAccessor->getValue($this->trackingVisit, $property)
            );
        }

        $propertyAccessor->setValue($this->trackingVisit, $property, $value);

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->trackingVisit, $property)
        );
    }

    public function propertyProvider(): array
    {
        $website = new TrackingWebsite();
        $date = new \DateTime();

        return [
            ['visitorUid', 'a0cde23', 'a0cde23'],
            ['userIdentifier', '215', '215'],
            ['firstActionTime', $date, $date],
            ['lastActionTime', $date, $date],
            ['parsedUid', 458, 458],
            ['parsingCount', 1, 1],
            ['ip', '127.0.0.1', '127.0.0.1'],
            ['identifierDetected', 1, 1, true],
            ['client', 'FF', 'FF'],
            ['os', 'MAC', 'MAC'],
            ['desktop', 1, 1],
            ['mobile', 0, 0],
            ['clientVersion', '35.0', '35.0'],
            ['clientType', 'browser', 'browser'],
            ['osVersion', '10.10', '10.10'],
            ['bot', 0, 0],
            ['trackingWebsite', $website, $website]
        ];
    }
}
