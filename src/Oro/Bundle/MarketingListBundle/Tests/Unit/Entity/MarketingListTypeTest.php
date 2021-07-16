<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Entity;

use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MarketingListTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MarketingListType
     */
    protected $entity;

    protected function setUp(): void
    {
        $this->entity = new MarketingListType(MarketingListType::TYPE_DYNAMIC);
    }

    protected function tearDown(): void
    {
        unset($this->entity);
    }

    /**
     * @dataProvider propertiesDataProvider
     * @param string $property
     * @param mixed $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($this->entity, $property, $value);
        $this->assertEquals($value, $accessor->getValue($this->entity, $property));
    }

    public function propertiesDataProvider()
    {
        return array(
            array('label', 'test'),
        );
    }

    public function testToString()
    {
        $this->entity->setLabel('test');
        $this->assertEquals('test', $this->entity->__toString());
    }
}
