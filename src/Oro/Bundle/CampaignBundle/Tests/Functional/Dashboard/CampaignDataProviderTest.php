<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Dashboard;

use Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignSalesData;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CampaignDataProviderTest extends WebTestCase
{
    /**
     * @var CampaignDataProvider
     */
    private $provider;

    protected function setUp(): void
    {
        if (!class_exists('Oro\Bundle\SalesBundle\OroSalesBundle')) {
            $this->markTestSkipped('CampaignDataProvider could be used only with SalesBundle installed');
        }

        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(
            [
                LoadCampaignSalesData::class,
            ]
        );
        $this->provider = $this->getContainer()->get('oro_campaign.test.dashboard.campaign_data_provider');
    }

    public function testGetCampaignLeadsData()
    {
        $actual = $this->provider->getCampaignLeadsData($this->getDateRange());
        $this->assertCount(1, $actual);
        $rowOne = reset($actual);

        $this->assertEquals(1, $rowOne['number']);
        $this->assertEquals('Campaign1', $rowOne['label']);
    }

    public function testGetCampaignOpportunitiesData()
    {
        $actual = $this->provider->getCampaignOpportunitiesData($this->getDateRange());
        $this->assertCount(1, $actual);
        $rowOne = reset($actual);

        $this->assertEquals(1, $rowOne['number']);
        $this->assertEquals('Campaign1', $rowOne['label']);
    }

    public function testGetCampaignsByCloseRevenueData()
    {
        $actual = $this->provider->getCampaignsByCloseRevenueData($this->getDateRange());
        $this->assertCount(1, $actual);
        $rowOne = reset($actual);

        $this->assertEquals(100, $rowOne['closeRevenue']);
        $this->assertEquals('Campaign1', $rowOne['label']);
    }

    private function getDateRange(): array
    {
        return [
            'type' => AbstractDateFilterType::TYPE_BETWEEN,
            'start' => new \DateTime('-1 day', new \DateTimeZone('UTC')),
            'end' => new \DateTime('+1 day', new \DateTimeZone('UTC')),
        ];
    }
}
