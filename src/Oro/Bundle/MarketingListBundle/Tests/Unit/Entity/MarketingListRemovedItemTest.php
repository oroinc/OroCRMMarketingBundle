<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Entity;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListRemovedItem;
use Oro\Component\Testing\ReflectionUtil;

class MarketingListRemovedItemTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListRemovedItem */
    private $entity;

    protected function setUp(): void
    {
        $this->entity = new MarketingListRemovedItem();
    }

    public function testGetId()
    {
        $this->assertNull($this->entity->getId());

        $value = 42;
        ReflectionUtil::setId($this->entity, $value);
        $this->assertSame($value, $this->entity->getId());
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
            ['entityId', 2],
            ['marketingList', $this->createMock(MarketingList::class)],
            ['createdAt', new \DateTime()],
        ];
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->entity->getCreatedAt());
        $this->entity->beforeSave();
        $this->assertInstanceOf(\DateTime::class, $this->entity->getCreatedAt());
    }
}
