<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\SegmentBundle\Entity\Segment;

class ConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $chainConfigurationProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $configProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    /**
     * @var ConfigurationProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->chainConfigurationProvider = $this
            ->getMockBuilder('Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface')
            ->getMock();
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Model\MarketingListHelper')
            ->disableOriginalConstructor()
            ->getMock();

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
            ->will($this->returnValue(1));
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
            ->will($this->returnValue(1));
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->will($this->returnValue(null));

        $this->provider->getConfiguration($gridName);
    }

    public function testGetConfigurationManualExceptionNoConfiguration()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grid not found for entity "\stdClass"');

        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $entityName = '\stdClass';

        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entityName));

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->will($this->returnValue(1));
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->will($this->returnValue($marketingList));

        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->will($this->returnValue(false));
        $this->configProvider->expects($this->never())
            ->method('getConfig');

        $this->provider->getConfiguration($gridName);
    }

    public function testGetConfigurationManualExceptionNoConfiguredGrid()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Grid not found for entity "\stdClass"');

        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $entityName = '\stdClass';

        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entityName));

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->will($this->returnValue(1));
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->will($this->returnValue($marketingList));

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->getMock();
        $config->expects($this->once())
            ->method('get')
            ->with('grid_name')
            ->will($this->returnValue(null));
        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->will($this->returnValue(true));
        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($entityName)
            ->will($this->returnValue($config));

        $this->provider->getConfiguration($gridName);
    }

    public function testGetConfigurationManual()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $actualGridName = 'test_grid';
        $entityName = '\stdClass';

        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entityName));

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->will($this->returnValue(1));
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->will($this->returnValue($marketingList));

        $config = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface')
            ->getMock();
        $config->expects($this->once())
            ->method('get')
            ->with('grid_name')
            ->will($this->returnValue($actualGridName));
        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->will($this->returnValue(true));
        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($entityName)
            ->will($this->returnValue($config));

        $configuration = $this->assertConfigurationGet($gridName, $actualGridName);

        $this->assertEquals($configuration, $this->provider->getConfiguration($gridName));
    }

    public function testGetConfigurationSegment()
    {
        $gridName = ConfigurationProvider::GRID_PREFIX . '1_postfix';
        $actualGridName = Segment::GRID_PREFIX . '2_postfix';

        $segment = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')
            ->disableOriginalConstructor()
            ->getMock();
        $segment->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));

        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));
        $marketingList->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $marketingList->expects($this->once())
            ->method('getSegment')
            ->will($this->returnValue($segment));

        $this->helper->expects($this->once())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->will($this->returnValue(1));
        $this->helper->expects($this->once())
            ->method('getMarketingList')
            ->with(1)
            ->will($this->returnValue($marketingList));

        $configuration = $this->assertConfigurationGet($gridName, $actualGridName);

        $this->assertEquals($configuration, $this->provider->getConfiguration($gridName));
    }

    protected function assertConfigurationGet($gridName, $actualGridName)
    {
        $configuration = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $configuration->expects($this->once())
            ->method('setName')
            ->with($gridName);
        $this->chainConfigurationProvider->expects($this->once())
            ->method('getConfiguration')
            ->with($actualGridName)
            ->will($this->returnValue($configuration));

        return $configuration;
    }

    public function testDoNotProcessInvalidSegmentGridName()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Marketing List id not found in "oro_segment_grid_" gridName.');

        $this->provider->getConfiguration(Segment::GRID_PREFIX);
    }
}
