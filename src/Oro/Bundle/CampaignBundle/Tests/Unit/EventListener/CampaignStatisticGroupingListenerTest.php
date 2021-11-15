<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Oro\Bundle\CampaignBundle\EventListener\CampaignStatisticGroupingListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\QueryDesignerBundle\Model\GroupByHelper;

class CampaignStatisticGroupingListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $marketingListHelper;

    /** @var GroupByHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $groupByHelper;

    /** @var CampaignStatisticGroupingListener */
    private $listener;

    protected function setUp(): void
    {
        $this->marketingListHelper = $this->createMock(MarketingListHelper::class);
        $this->groupByHelper = $this->createMock(GroupByHelper::class);

        $this->listener = new CampaignStatisticGroupingListener($this->marketingListHelper, $this->groupByHelper);
    }

    /**
     * @dataProvider applicableDataProvider
     */
    public function testIsApplicable(string $gridName, bool $hasCampaign, ?int $id, bool $expected)
    {
        $parametersBag = $this->createMock(ParameterBag::class);
        $parametersBag->expects($this->once())
            ->method('has')
            ->with('emailCampaign')
            ->willReturn($hasCampaign);

        if ($hasCampaign) {
            $this->marketingListHelper->expects($this->once())
                ->method('getMarketingListIdByGridName')
                ->with($gridName)
                ->willReturn($id);
        }

        $this->assertEquals($expected, $this->listener->isApplicable($gridName, $parametersBag));
    }

    public function applicableDataProvider(): array
    {
        return [
            ['test_grid', false, null, false],
            ['test_grid', true, null, false],
            ['test_grid', true, 1, true],
        ];
    }

    /**
     * @dataProvider preBuildDataProvider
     */
    public function testOnPreBuild(array $select, ?string $groupBy, string $expected)
    {
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $parameters = ['emailCampaign' => 1];
        $config = DatagridConfiguration::create(
            [
                'name'   => $gridName,
                'source' => [
                    'query' => [
                        'select'  => $select,
                        'groupBy' => $groupBy
                    ]
                ]
            ]
        );

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName))
            ->willReturn(1);
        $this->groupByHelper->expects($this->once())
            ->method('getGroupByFields')
            ->with($groupBy, $select)
            ->willReturn(explode(',', $expected));

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag($parameters)));

        $this->assertSame($expected, $config->offsetGetByPath('[source][query][groupBy]'));
    }

    public function testOnPreBuildNotApplicable()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $config = DatagridConfiguration::createNamed('test_grid', []);

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName));
        $this->groupByHelper->expects($this->never())
            ->method('getGroupByFields');

        $this->listener->onPreBuild(new PreBuild($config, new ParameterBag([])));
    }

    public function preBuildDataProvider(): array
    {
        return [
            'no fields' => [
                'selects'    => [],
                'groupBy'    => null,
                'expected'   => '',
            ],
            'group by no selects' => [
                'selects'    => [],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing',
            ],
            'select no group by' => [
                'selects'    => ['alias.field'],
                'groupBy'    => null,
                'expected'   => 'alias.field',
            ],
            'select and group by' => [
                'selects'    => ['alias.field', 'alias.matchedFields as c1'],
                'groupBy'    => 'alias.existing',
                'expected'   => 'alias.existing,alias.field,c1',
            ]
        ];
    }
}
