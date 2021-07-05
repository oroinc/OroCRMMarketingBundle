<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Placeholder;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MarketingActivityBundle\Entity\Repository\MarketingActivityRepository;
use Oro\Bundle\MarketingActivityBundle\Placeholder\PlaceholderFilter;
use Oro\Bundle\MarketingActivityBundle\Tests\Unit\Stub\EntityStub;

class PlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var EntityProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $entityProvider;

    /** @var FeatureChecker|\PHPUnit\Framework\MockObject\MockObject */
    private $featureChecker;

    /** @var PlaceholderFilter */
    private $filter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->entityProvider = $this->createMock(EntityProvider::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

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
            ->willReturnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            });
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);
        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(true);

        $this->assertFalse($this->filter->isApplicable(null));
        $this->assertFalse($this->filter->isApplicable(new EntityStub()));
    }

    public function testIsApplicableNonManageableEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->willReturnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            });

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(false);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(true);

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->willReturnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            });

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([['name' => EntityStub::class]]);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(true);

        $this->assertTrue($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableWithDisabledFeature()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->willReturnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            });

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(false);

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsApplicableWrongEntity()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->willReturnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            });

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });
        $this->doctrineHelper->expects($this->any())
            ->method('isManageableEntity')
            ->willReturn(true);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([['name' => 'TestEntity']]);

        $this->featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(true);

        $this->assertFalse($this->filter->isApplicable(new EntityStub(1)));
    }

    public function testIsSummaryApplicable()
    {
        $this->doctrineHelper->expects($this->any())
            ->method('isNewEntity')
            ->willReturnCallback(function ($entity) {
                if (method_exists($entity, 'getId')) {
                    return !(bool)$entity->getId();
                }

                return false;
            });

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });

        $entityRepository = $this->createMock(MarketingActivityRepository::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($entityRepository);
        $entityRepository->expects($this->once())
            ->method('getMarketingActivitySummaryCountByCampaign')
            ->willReturn(true);

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(true);

        $this->assertTrue($this->filter->isSummaryApplicable(1));
    }

    public function testSummaryIsNotApplicable()
    {
        $this->doctrineHelper->expects($this->never())
            ->method('isNewEntity');

        $this->doctrineHelper->expects($this->never())
            ->method('getEntityClass')
            ->willReturnCallback(function ($entity) {
                return ClassUtils::getClass($entity);
            });

        $entityRepository = $this->createMock(MarketingActivityRepository::class);
        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepository')
            ->willReturn($entityRepository);
        $entityRepository->expects($this->never())
            ->method('getMarketingActivitySummaryCountByCampaign')
            ->willReturn(true);

        $this->featureChecker->expects($this->once())
            ->method('isFeatureEnabled')
            ->with('marketingactivity', null)
            ->willReturn(false);

        $this->assertFalse($this->filter->isSummaryApplicable(1));
    }

    public function testFeatureToggleable()
    {
        $this->assertInstanceOf(FeatureToggleableInterface::class, $this->filter);
    }
}
