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
use Oro\Bundle\SegmentBundle\Tests\Unit\Stub\Entity\SegmentStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurationProviderTest extends TestCase
{
    private ConfigurationProviderInterface|MockObject $chainConfigurationProvider;

    private ConfigProvider|MockObject $configProvider;

    private MarketingListHelper|MockObject $helper;

    private ConfigurationProvider $provider;

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

    public function testIsApplicable(): void
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        self::assertTrue($this->provider->isApplicable($gridName));
    }

    public function testIsValidConfiguration(): void
    {
        $id = 1;
        $gridName = ConfigurationProvider::GRID_PREFIX . $id . '_postfix';
        $marketingList = $this->assertMarketingList($id);

        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn($id);

        $this->helper->expects(self::once())
            ->method('getMarketingList')
            ->with($id)
            ->willReturn($marketingList);

        self::assertTrue($this->provider->isValidConfiguration($gridName));
    }

    public function testIsNotValidConfiguration(): void
    {
        $id = 1;
        $gridName = ConfigurationProvider::GRID_PREFIX . $id . '_postfix';
        $marketingList = $this->assertMarketingList($id);

        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(null);

        $this->helper->expects(self::never())
            ->method('getMarketingList')
            ->with($id)
            ->willReturn($marketingList);

        self::assertFalse($this->provider->isValidConfiguration($gridName));
    }

    public function testGetConfigurationException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Marketing List with id "1" not found.');

        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects(self::once())
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
        $marketingList->expects(self::once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects(self::once())
            ->method('getEntity')
            ->willReturn($entityName);

        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects(self::once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $this->configProvider->expects(self::once())
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
        $marketingList->expects(self::once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects(self::once())
            ->method('getEntity')
            ->willReturn($entityName);

        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects(self::once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $config = $this->createMock(ConfigInterface::class);
        $config->expects(self::once())
            ->method('get')
            ->with('grid_name')
            ->willReturn(null);
        $this->configProvider->expects(self::once())
            ->method('hasConfig')
            ->willReturn(true);
        $this->configProvider->expects(self::once())
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
        $marketingList->expects(self::once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects(self::once())
            ->method('getEntity')
            ->willReturn($entityName);

        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects(self::once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $config = $this->createMock(ConfigInterface::class);
        $config->expects(self::once())
            ->method('get')
            ->with('grid_name')
            ->willReturn($actualGridName);
        $this->configProvider->expects(self::once())
            ->method('hasConfig')
            ->willReturn(true);
        $this->configProvider->expects(self::once())
            ->method('getConfig')
            ->with($entityName)
            ->willReturn($config);

        $configuration = self::assertConfigurationGet($gridName, $actualGridName);

        self::assertEquals($configuration, $this->provider->getConfiguration($gridName));
    }

    public function testGetConfigurationSegment(): void
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $actualGridName = Segment::GRID_PREFIX . '2_postfix';

        $segment = $this->createMock(Segment::class);
        $segment->expects(self::once())
            ->method('getId')
            ->willReturn(2);

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects(self::once())
            ->method('isManual')
            ->willReturn(false);
        $marketingList->expects(self::once())
            ->method('getId')
            ->willReturn(1);
        $marketingList->expects(self::once())
            ->method('getSegment')
            ->willReturn($segment);

        $this->helper->expects(self::once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn(1);
        $this->helper->expects(self::once())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $configuration = self::assertConfigurationGet($gridName, $actualGridName);

        self::assertEquals($configuration, $this->provider->getConfiguration($gridName));
    }

    private function assertConfigurationGet($gridName, $actualGridName): DatagridConfiguration|MockObject
    {
        $configuration = $this->createMock(DatagridConfiguration::class);
        $configuration->expects(self::once())
            ->method('setName')
            ->with($gridName);
        $this->chainConfigurationProvider->expects(self::once())
            ->method('getConfiguration')
            ->with($actualGridName)
            ->willReturn($configuration);

        return $configuration;
    }

    private function assertMarketingList(int $segmentId): MarketingList
    {
        $marketingList = new MarketingList();
        $segment = new SegmentStub($segmentId);
        $marketingList->setSegment($segment);
        return $marketingList;
    }

    public function testDoNotProcessInvalidSegmentGridName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Marketing List id not found in "oro_segment_grid_" gridName.');

        $this->provider->getConfiguration(Segment::GRID_PREFIX);
    }
}
