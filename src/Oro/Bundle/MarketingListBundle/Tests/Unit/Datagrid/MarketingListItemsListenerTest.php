<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\EventListener\MixinListener;
use Oro\Bundle\MarketingListBundle\Datagrid\MarketingListItemsListener;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\SegmentBundle\Entity\Segment;

class MarketingListItemsListenerTest extends \PHPUnit\Framework\TestCase
{
    private const MIXIN_NAME = 'new-mixin-for-test-grid';

    /** @var MarketingListHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $marketingListHelper;

    /** @var MarketingListItemsListener */
    private $listener;

    protected function setUp(): void
    {
        $this->marketingListHelper = $this->createMock(MarketingListHelper::class);

        $this->listener = new MarketingListItemsListener($this->marketingListHelper);
    }

    /**
     * @dataProvider buildAfterDataProvider
     */
    public function testOnBuildAfter(string $gridName, bool $useDataSource, bool $hasParameter)
    {
        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $event = $this->createMock(BuildAfter::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $event->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);
        $datagrid->expects($this->once())
            ->method('getName')
            ->willReturn($gridName);

        $parameters = [];
        if ($hasParameter) {
            $parameters = [MixinListener::GRID_MIXIN => self::MIXIN_NAME];
        }

        $datagrid->expects($this->once())
            ->method('getParameters')
            ->willReturn(new ParameterBag($parameters));

        /** @var MarketingList $marketingList */
        if ($hasParameter) {
            $this->marketingListHelper->expects($this->exactly(1 + (int)$useDataSource))
                ->method('getMarketingListIdByGridName')
                ->with($this->equalTo($gridName))
                ->willReturn($marketingList->getId());

            if ((int)$useDataSource) {
                $this->marketingListHelper->expects($this->exactly((int)$useDataSource))
                    ->method('getMarketingList')
                    ->with($this->equalTo($marketingList->getId()))
                    ->willReturn($marketingList);
            } else {
                $this->marketingListHelper->expects($this->never())
                    ->method('getMarketingList');
            }
        }

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->any())
            ->method('addSelect')
            ->willReturnSelf();
        $qb->expects($this->any())
            ->method('setParameter')
            ->willReturnSelf();

        $dataSource = $this->createMock(OrmDatasource::class);
        if ($hasParameter) {
            $dataSource->expects($this->exactly((int)$useDataSource))
                ->method('getQueryBuilder')
                ->willReturn($qb);
            $datagrid->expects($this->once())
                ->method('getDatasource')
                ->willReturn($useDataSource ? $dataSource : null);
        }

        $this->listener->onBuildAfter($event);
    }

    public function buildAfterDataProvider(): array
    {
        return [
            ['gridName', false, false],
            ['gridName', false, true],
            ['gridName', true, false],
            ['gridName', true, true],
            [Segment::GRID_PREFIX, false, false],
            [Segment::GRID_PREFIX, false, true],
            [Segment::GRID_PREFIX, true, false],
            [Segment::GRID_PREFIX, true, true],
            [Segment::GRID_PREFIX . '1', false, false],
            [Segment::GRID_PREFIX . '1', false, true],
            [Segment::GRID_PREFIX . '1', true, false],
            [Segment::GRID_PREFIX . '1', true, true],
        ];
    }

    /**
     * @dataProvider onBuildBeforeDataProvider
     */
    public function testOnBuildBefore(string $gridName, bool $hasParameter, bool $isApplicable, bool $expected)
    {
        $event = $this->createMock(BuildBefore::class);
        $datagrid = $this->createMock(DatagridInterface::class);
        $event->expects($this->once())
            ->method('getDatagrid')
            ->willReturn($datagrid);

        $parameters = [];
        if ($hasParameter) {
            $parameters = [MixinListener::GRID_MIXIN => self::MIXIN_NAME];

            $this->marketingListHelper->expects($this->once())
                ->method('getMarketingListIdByGridName')
                ->with($this->equalTo($gridName))
                ->willReturn((int)$isApplicable);
        }

        $datagrid->expects($this->once())
            ->method('getParameters')
            ->willReturn(new ParameterBag($parameters));
        $datagrid->expects($this->once())
            ->method('getName')
            ->willReturn($gridName);

        if ($expected) {
            $event->expects($this->once())
                ->method('stopPropagation');
        }

        $this->listener->onBuildBefore($event);
    }

    public function onBuildBeforeDataProvider(): array
    {
        return [
            ['gridName', false, false, false],
            ['gridName', false, true, false],
            ['gridName', true, false, false],
            ['gridName', true, true, true],
        ];
    }
}
