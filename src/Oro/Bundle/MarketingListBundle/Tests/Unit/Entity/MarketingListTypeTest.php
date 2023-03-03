<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class MarketingListTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListType */
    private $entity;

    protected function setUp(): void
    {
        $this->entity = new MarketingListType(MarketingListType::TYPE_DYNAMIC);
    }

    /**
     * @dataProvider propertiesDataProvider
     */
    public function testSettersAndGetters(string $property, mixed $value)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($this->entity, $property, $value);
        $this->assertEquals($value, $accessor->getValue($this->entity, $property));
    }

    public function propertiesDataProvider(): array
    {
        return [
            ['label', 'test'],
        ];
    }

    public function testToString()
    {
        $this->entity->setLabel('test');
        $this->assertEquals('test', $this->entity->__toString());
    }
}
