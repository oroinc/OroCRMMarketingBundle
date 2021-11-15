<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\SegmentBundle\Entity\Segment;

class ConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigurationProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $chainConfigurationProvider;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var MarketingListHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $helper;

    /** @var ConfigurationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->chainConfigurationProvider = $this->createMock(ConfigurationProviderInterface::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->helper = $this->createMock(MarketingListHelper::class);

        $this->provider = new ConfigurationProvider(
            $this->chainConfigurationProvider,
            $this->configProvider,
            $this->helper
        );
    }

    public function testIsApplicable()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->assertTrue($this->provider->isApplicable($gridName));
    }

    public function testGetConfigurationException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Marketing List with id "1" not found.');

        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn(null);

        $this->provider->getConfiguration($gridName);
    }

    public function testGetConfigurationManualExceptionNoConfiguration()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grid not found for entity "stdClass"');

        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $entityName = \stdClass::class;

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->willReturn($entityName);

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(false);
        $this->configProvider->expects($this->never())
            ->method('getConfig');

        $this->provider->getConfiguration($gridName);
    }

    public function testGetConfigurationManualExceptionNoConfiguredGrid()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grid not found for entity "stdClass"');

        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $entityName = \stdClass::class;

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->willReturn($entityName);

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $config = $this->createMock(ConfigInterface::class);
        $config->expects($this->once())
            ->method('get')
            ->with('grid_name')
            ->willReturn(null);
        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(true);
        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($entityName)
            ->willReturn($config);

        $this->provider->getConfiguration($gridName);
    }

    public function testGetConfigurationManual()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $actualGridName = 'test_grid';
        $entityName = \stdClass::class;

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->willReturn($entityName);

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $config = $this->createMock(ConfigInterface::class);
        $config->expects($this->once())
            ->method('get')
            ->with('grid_name')
            ->willReturn($actualGridName);
        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->willReturn(true);
        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($entityName)
            ->willReturn($config);

        $configuration = $this->assertConfigurationGet($gridName, $actualGridName);

        $this->assertEquals($configuration, $this->provider->getConfiguration($gridName));
    }

    public function testGetConfigurationSegment()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $actualGridName = Segment::GRID_PREFIX . '2_postfix';

        $segment = $this->createMock(Segment::class);
        $segment->expects($this->once())
            ->method('getId')
            ->willReturn(2);

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(false);
        $marketingList->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $marketingList->expects($this->once())
            ->method('getSegment')
            ->willReturn($segment);

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $configuration = $this->assertConfigurationGet($gridName, $actualGridName);

        $this->assertEquals($configuration, $this->provider->getConfiguration($gridName));
    }

    private function assertConfigurationGet($gridName, $actualGridName)
    {
        $configuration = $this->createMock(DatagridConfiguration::class);
        $configuration->expects($this->once())
            ->method('setName')
            ->with($gridName);
        $this->chainConfigurationProvider->expects($this->once())
            ->method('getConfiguration')
            ->with($actualGridName)
            ->willReturn($configuration);

        return $configuration;
    }

    public function testDoNotProcessInvalidSegmentGridName()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Marketing List id not found in "oro_segment_grid_" gridName.');

        $this->provider->getConfiguration(Segment::GRID_PREFIX);
    }
}
