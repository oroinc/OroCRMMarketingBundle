<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Dashboard;

use Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignSalesData;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CampaignDataProviderTest extends WebTestCase
{
    private CampaignDataProvider $provider;

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

    /**
     * @dataProvider campaignLeadDataProvider
     */
    public function testGetCampaignLeadsData(array $request, array $response): void
    {
        $actual = $this->provider->getCampaignLeadsData(
            $request['dateRange'],
            $request['hideCampaign'],
            $request['maxResults']
        );

        $this->assertCount(min($request['maxResults'], count($response)), $actual);
        foreach ($actual as $item) {
            $this->assertEquals($response[$item['label']], $item['number']);
        }
    }

    public function campaignLeadDataProvider(): array
    {
        return [
            'With a date range' => [
                'request' => [
                    'dateRange' => $this->getDateRange(),
                    'hideCampaign' => true,
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 1,
                    'Campaign2' => 1
                ]
            ],
            'With date range all time' => [
                'request' => [
                    'dateRange' => $this->getDateRangeAllTime(),
                    'hideCampaign' => true,
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 2,
                    'Campaign2' => 2
                ]
            ],
            'With date range all time and not to hide campaign without lead' => [
                'request' => [
                    'dateRange' => $this->getDateRangeAllTime(),
                    'hideCampaign' => false,
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 2,
                    'Campaign2' => 2,
                    'Campaign3' => 0
                ]
            ],
            'With date range all time but only 1 result' => [
                'request' => [
                    'dateRange' => $this->getDateRangeAllTime(),
                    'hideCampaign' => false,
                    'maxResults' => 1
                ],
                'response' => [
                    'Campaign1' => 2,
                    'Campaign2' => 2,
                    'Campaign3' => 0
                ]
            ],
        ];
    }

    /**
     * @dataProvider campaignOpportunitiesDataProvider
     */
    public function testGetCampaignOpportunitiesData(array $request, array $response): void
    {
        $actual = $this->provider->getCampaignOpportunitiesData($request['dateRange'], $request['maxResults']);

        $this->assertCount(min($request['maxResults'], count($response)), $actual);
        foreach ($actual as $item) {
            $this->assertEquals($response[$item['label']], $item['number']);
        }
    }

    public function campaignOpportunitiesDataProvider(): array
    {
        return [
            'With a date range' => [
                'request' => [
                    'dateRange' => $this->getDateRange(),
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 1,
                    'Campaign2' => 1
                ]
            ],
            'With date range all time' => [
                'request' => [
                    'dateRange' => $this->getDateRangeAllTime(),
                    'hideCampaign' => true,
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 2,
                    'Campaign2' => 2
                ]
            ],
            'With date range all time but only 1 result' => [
                'request' => [
                    'dateRange' => $this->getDateRangeAllTime(),
                    'maxResults' => 1
                ],
                'response' => [
                    'Campaign1' => 2,
                    'Campaign2' => 2
                ]
            ]
        ];
    }

    /**
     * @dataProvider campaignsByCloseRevenueDataProvider
     */
    public function testGetCampaignsByCloseRevenueData(array $request, array $response): void
    {
        $actual = $this->provider->getCampaignsByCloseRevenueData($request['dateRange'], $request['maxResults']);

        $this->assertCount(min($request['maxResults'], count($response)), $actual);
        foreach ($actual as $item) {
            $this->assertEquals($response[$item['label']], $item['closeRevenue']);
        }
    }

    public function campaignsByCloseRevenueDataProvider(): array
    {
        return [
            'With a date range' => [
                'request' => [
                    'dateRange' => $this->getDateRange(),
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 100,
                    'Campaign2' => 100
                ]
            ],
            'With date range all time' => [
                'request' => [
                    'dateRange' => $this->getDateRangeAllTime(),
                    'hideCampaign' => true,
                    'maxResults' => 5
                ],
                'response' => [
                    'Campaign1' => 200,
                    'Campaign2' => 200
                ]
            ],
            'With a date range but only 1 result' => [
                'request' => [
                    'dateRange' => $this->getDateRange(),
                    'maxResults' => 1
                ],
                'response' => [
                    'Campaign1' => 100,
                    'Campaign2' => 100
                ]
            ]
        ];
    }

    private function getDateRange(): array
    {
        return [
            'type' => AbstractDateFilterType::TYPE_BETWEEN,
            'start' => new \DateTime('-1 day', new \DateTimeZone('UTC')),
            'end' => new \DateTime('+1 day', new \DateTimeZone('UTC')),
        ];
    }

    private function getDateRangeAllTime(): array
    {
        return [
            'type' => AbstractDateFilterType::TYPE_ALL_TIME,
            'start' => null,
            'end' => null,
            'part' => 'all_time"',
            'prev_start' => null,
            'prev_end' => null
        ];
    }
}
