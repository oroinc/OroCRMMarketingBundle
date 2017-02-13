<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Placeholder;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingActivityBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\MarketingActivityBundle\Tests\Unit\Stub\EntityStub;

class PlaceholderFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityProvider */
    protected $entityProvider;

    /** @var  PlaceholderFilter */
    protected $filter;

    public function setUp()
    {
        $this->doctrineHelper = $this
            ->getMockBuilder('\Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->setMethods([
                'isNewEntity',
                'isManageableEntity',
                'getEntityClass',
                'getEntityRepository',
                'getMarketingActivitySummaryCountByCampaign'
            ])
            ->getMock();

        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                throw new \RuntimeException('Something wrong');
            }));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));

        $this->entityProvider = $this->getMockBuilder('\Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()
            ->setMethods(['getEntities'])
            ->getMock();

        $this->filter = new PlaceholderFilter(
            $this->doctrineHelper,
            $this->entityProvider
        );
    }

    public function testIsApplicableEmptyEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);
        $this->assertFalse($this->filter->isApplicable(null));
        $this->assertFalse($this->filter->isApplicable(new EntityStub()));
    }

    public function testIsApplicableNonManageableEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(false);

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([['name' => 'Oro\Bundle\MarketingActivityBundle\Tests\Unit\Stub\EntityStub']]);

        $this->assertTrue($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableWrongEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([['name' => 'TestEntity']]);

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function isSummaryApplicable()
    {
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturnSelf();

        $this->doctrineHelper->expects($this->once())
            ->method('getMarketingActivitySummaryCountByCampaign')
            ->willReturn(true);

        $this->assertTrue($this->filter->isSummaryApplicable(1));
    }
}
