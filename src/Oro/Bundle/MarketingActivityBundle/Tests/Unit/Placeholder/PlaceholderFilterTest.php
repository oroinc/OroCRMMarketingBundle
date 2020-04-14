<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Placeholder;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MarketingActivityBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\MarketingActivityBundle\Tests\Unit\Stub\EntityStub;

class PlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    protected $doctrineHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityProvider */
    protected $entityProvider;

    /** @var  PlaceholderFilter */
    protected $filter;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $featureChecker;

    protected function setUp(): void
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

        $this->entityProvider = $this->getMockBuilder('\Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()
            ->setMethods(['getEntities'])
            ->getMock();

        $this->featureChecker = $this->getMockBuilder(FeatureChecker::class)
            ->setMethods(['isFeatureEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filter = new PlaceholderFilter(
            $this->doctrineHelper,
            $this->entityProvider
        );
        $this->filter->setFeatureChecker($this->featureChecker);
        $this->filter->addFeature('marketingactivity');
    }

    public function testIsApplicableEmptyEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            }));
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);
        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(true));

        $this->assertFalse($this->filter->isApplicable(null));
        $this->assertFalse($this->filter->isApplicable(new EntityStub()));
    }

    public function testIsApplicableNonManageableEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            }));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(false);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(true));

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            }));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([['name' => 'Oro\Bundle\MarketingActivityBundle\Tests\Unit\Stub\EntityStub']]);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(true));

        $this->assertTrue($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableWithDisabledFeature()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            }));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(false));

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableWrongEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            }));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([['name' => 'TestEntity']]);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(true));

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsSummaryApplicable()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->will($this->returnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            }));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturnSelf();

        $this->doctrineHelper->expects($this->once())
            ->method('getMarketingActivitySummaryCountByCampaign')
            ->willReturn(true);

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(true));

        $this->assertTrue($this->filter->isSummaryApplicable(1));
    }

    public function testSummaryIsNotApplicable()
    {
        $this->doctrineHelper->expects($this->never())
            ->method('isNewEntity');

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityClass')
            ->will($this->returnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            }));
        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepository')
            ->willReturnSelf();

        $this->doctrineHelper->expects($this->never())
            ->method('getMarketingActivitySummaryCountByCampaign')
            ->willReturn(true);

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->will($this->returnValue(false));

        $this->assertFalse($this->filter->isSummaryApplicable(1));
    }

    public function testFeatureToggleable()
    {
        $this->assertInstanceOf(FeatureToggleableInterface::class, $this->filter);
    }
}
