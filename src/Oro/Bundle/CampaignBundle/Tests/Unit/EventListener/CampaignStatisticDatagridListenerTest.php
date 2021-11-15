<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\EventListener\CampaignStatisticDatagridListener;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\EventListener\MixinListener;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;

class CampaignStatisticDatagridListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $marketingListHelper;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var CampaignStatisticDatagridListener */
    private $listener;

    protected function setUp(): void
    {
        $this->marketingListHelper = $this->createMock(MarketingListHelper::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->listener = new CampaignStatisticDatagridListener($this->marketingListHelper, $this->registry);
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
    public function testOnPreBuildSentCampaign(bool $isSent, string $expectedMixin)
    {
        $id = 1;
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $parameters = new ParameterBag(['emailCampaign' => $id]);
        $config = DatagridConfiguration::create(
            [
                'name'   => $gridName,
                'source' => [
                    'query' => [
                        'where' => '1 = 0'
                    ]
                ]
            ]
        );

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName))
            ->willReturn($id);

        $marketingList = $this->createMock(EmailCampaign::class);
        $marketingList->expects($this->once())
            ->method('isSent')
            ->willReturn($isSent);
        $this->assertEntityFind($id, $marketingList);

        $this->listener->onPreBuild(new PreBuild($config, $parameters));

        if ($isSent) {
            $this->assertEmpty($config->offsetGetByPath('[source][query][where]'));
        }

        $this->assertEquals($expectedMixin, $parameters->get(MixinListener::GRID_MIXIN));
    }

    public function preBuildDataProvider(): array
    {
        return [
            'not sent' => [false, CampaignStatisticDatagridListener::MIXIN_UNSENT_NAME],
            'sent' => [true, CampaignStatisticDatagridListener::MIXIN_SENT_NAME],
        ];
    }

    private function assertEntityFind(int $id, object $entity): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($entity);

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCampaignBundle:EmailCampaign')
            ->willReturn($repository);
    }

    public function testOnPreBuildNotApplicable()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX;
        $config = DatagridConfiguration::createNamed('test_grid', []);

        $event = new PreBuild($config, new ParameterBag([]));

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($this->equalTo($gridName));
        $this->registry->expects($this->never())
            ->method('getRepository');

        $this->listener->onPreBuild($event);
    }
}
