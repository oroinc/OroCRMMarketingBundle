<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListRemovedItem;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListUnsubscribedItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MarketingListTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingList */
    private $entity;

    protected function setUp(): void
    {
        $this->entity = new MarketingList();
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
        $type = $this->createMock(MarketingListType::class);

        return [
            ['name', 'test'],
            ['description', 'test'],
            ['entity', 'Test'],
            ['type', $type],
            ['segment', $this->createMock(Segment::class)],
            ['owner', $this->createMock(User::class)],
            ['organization', $this->createMock(Organization::class)],
            ['lastRun', new \DateTime()],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
        ];
    }

    public function testMarketingListItems()
    {
        $this->assertCollectionMethods(
            MarketingListItem::class,
            'MarketingListItem'
        );
    }

    public function testMarketingListRemovedItems()
    {
        $this->assertCollectionMethods(
            MarketingListRemovedItem::class,
            'MarketingListRemovedItem'
        );
    }

    public function testMarketingListUnsubscribedItems()
    {
        $this->assertCollectionMethods(
            MarketingListUnsubscribedItem::class,
            'MarketingListUnsubscribedItem'
        );
    }

    public function testToString()
    {
        $this->entity->setName('test');
        $this->assertEquals('test', $this->entity->__toString());
    }

    public function testSetDefinition()
    {
        $definition = 'test';
        $segment = $this->createMock(Segment::class);
        $segment->expects($this->once())
            ->method('setDefinition')
            ->with($definition);
        $this->entity->setSegment($segment);
        $this->entity->setDefinition($definition);
    }

    public function testGetDefinition()
    {
        $definition = 'test';
        $segment = $this->createMock(Segment::class);
        $segment->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definition);

        $this->assertNull($this->entity->getDefinition());
        $this->entity->setSegment($segment);
        $this->assertEquals($definition, $this->entity->getDefinition());
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->entity->getCreatedAt());
        $this->assertNull($this->entity->getUpdatedAt());
        $this->entity->beforeSave();
        $this->assertInstanceOf(\DateTime::class, $this->entity->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $this->entity->getUpdatedAt());
    }

    public function testDoUpdate()
    {
        $this->assertNull($this->entity->getUpdatedAt());
        $this->entity->doUpdate();
        $this->assertInstanceOf(\DateTime::class, $this->entity->getUpdatedAt());
    }

    private function assertCollectionMethods($entityClass, $entityShortName)
    {
        $addMethodName = 'add' . $entityShortName;
        $removeMethodName = 'remove' . $entityShortName;
        $resetMethodName = 'reset' . $entityShortName . 's';
        $getMethodName = 'get' . $entityShortName . 's';

        $itemOne = $this->createMock($entityClass);
        $itemTwo = $this->createMock($entityClass);

        $this->assertInstanceOf(Collection::class, $this->entity->{$getMethodName}());
        $this->assertCount(0, $this->entity->{$getMethodName}());
        $this->entity->{$addMethodName}($itemOne);
        $this->entity->{$addMethodName}($itemTwo);
        $this->assertCount(2, $this->entity->{$getMethodName}());
        $this->entity->{$removeMethodName}($itemOne);
        $this->assertCount(1, $this->entity->{$getMethodName}());
        $this->assertEquals($itemTwo, $this->entity->{$getMethodName}()->first());
        $this->entity->{$resetMethodName}([]);
        $this->assertCount(0, $this->entity->{$getMethodName}());
        $this->entity->{$resetMethodName}([$itemOne, $itemTwo]);
        $this->assertCount(2, $this->entity->{$getMethodName}());
    }

    public function testIsManualWithSegment()
    {
        $ml = new MarketingList();
        $segment = new Segment();
        $ml->setSegment($segment);

        $this->assertNull($ml->getType());
        $this->assertFalse($ml->isManual());
    }

    public function testIsManualWithoutSegmentAndType()
    {
        $ml = new MarketingList();

        $this->assertNull($ml->getType());
        $this->assertFalse($ml->isManual());
    }

    public function testIsManualWithManualType()
    {
        $ml = new MarketingList();
        $type = new MarketingListType(MarketingListType::TYPE_MANUAL);
        $ml->setType($type);

        $this->assertNotNull($ml->getType());
        $this->assertTrue($ml->isManual());
    }

    public function testIsManualWithNoNManualType()
    {
        $ml = new MarketingList();
        $type = new MarketingListType(MarketingListType::TYPE_DYNAMIC);
        $ml->setType($type);

        $this->assertNotNull($ml->getType());
        $this->assertFalse($ml->isManual());
    }
}
