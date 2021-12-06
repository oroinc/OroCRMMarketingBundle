<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Functional\Controller;

use Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures\LoadTestEntityData;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class MarketingActivityControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures(
            [
                'Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures\LoadMarketingActivityData'
            ]
        );
    }

    public function testSummary()
    {
        $campaign = $this->getReference('Campaign.Campaign1');
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_marketing_activity_widget_summary', ['campaignId' => $campaign->getId()])
        );
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);

        $expectedTypes = [
            'Send' => '1',
            'Open' => '1',
            'Click' => '3',
            'Soft Bounce' => '1',
            'Hard Bounce' => '1',
            'Unsubscribe' => '2',
        ];
        foreach ($expectedTypes as $type => $value) {
            $typeXpath = '//div[contains(@class, "attribute-item")]/label[text() = "' . $type . '"]/'
                . 'following-sibling::div/div[contains(@class, "control-label")]';
            $this->assertEquals($value, $crawler->filterXPath($typeXpath)->text());
        }
    }

    public function testWidget()
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_marketing_activity_widget_marketing_activities',
                [
                    'entityClass' => TestActivity::class,
                    'entityId'    => $this->getReference(LoadTestEntityData::TEST_ENTITY_1)->getId(),
                    '_widgetContainer' => 'widget'
                ]
            )
        );
        $result  = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertCount(1, $crawler->filter('div.widget-content.marketing-activities'));

        $componentOptionsJson = $crawler->filterXPath('//div[contains(@data-page-component-module,'
            . ' "oromarketingactivity/js/app/components/marketing-activities-section-component")]'
            . '/@data-page-component-options')
            ->text();
        $componentOptions = json_decode($componentOptionsJson, true, 512, JSON_THROW_ON_ERROR);
        $this->assertNotEmpty($componentOptions['activityListData']);
        $activityListData = json_decode($componentOptions['activityListData'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals(2, $activityListData['count']);
        $campaign1 = $this->getReference('Campaign.Campaign1');
        $campaign2 = $this->getReference('Campaign.Campaign2');
        $expected = [
            [
                'campaignName' => $campaign1->getName(),
                'eventType' => 'Open',
                'editable' => false,
                'removable' => false,
            ],
            [
                'campaignName' => $campaign2->getName(),
                'eventType' => 'Click',
                'editable' => false,
                'removable' => false,
            ]
        ];
        foreach ($expected as $key => $value) {
            foreach ($value as $dataKey => $dataValue) {
                $this->assertEquals($dataValue, $activityListData['data'][$key][$dataKey]);
            }
        }

        $expectedCampaignFilter = [
            $campaign1->getId() => $campaign1->getName(),
            $campaign2->getId() => $campaign2->getName(),
        ];
        $this->assertEquals($expectedCampaignFilter, $componentOptions['activityListOptions']['campaignFilterValues']);
    }

    public function testInfo()
    {
        $campaign = $this->getReference('Campaign.Campaign1');
        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_marketing_activity_widget_marketing_activities_info',
                [
                    'id' => $campaign->getId(),
                    'targetActivityClass' => TestActivity::class,
                    'targetActivityId' => $this->getReference(LoadTestEntityData::TEST_ENTITY_1)->getId()
                ]
            )
        );
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $expectedTypes = [
            'Send' => '1',
            'Open' => '1',
            'Click' => '1',
        ];
        foreach ($expectedTypes as $type => $value) {
            $typeXpath = '//div[contains(@class, "attribute-item")]/label[text() = "' . $type . '"]/'
                . 'following-sibling::div/div[contains(@class, "control-label")]';
            $this->assertEquals($value, $crawler->filterXPath($typeXpath)->text());
        }
        $this->assertCount(1, $crawler->filter('div.marketing-activities-section-data-grid'));
    }

    public function testGetList()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_marketing_activity_widget_marketing_activities_list',
                [
                    'entityClass' => TestActivity::class,
                    'entityId'    => $this->getReference(LoadTestEntityData::TEST_ENTITY_2)->getId()
                ]
            )
        );
        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertCount(1, $result['data']);
        $expected = [
            [
                'campaignName' => $this->getReference('Campaign.Campaign1')->getName(),
                'eventType' => 'Unsubscribe',
                'editable' => false,
                'removable' => false,
            ],
        ];
        foreach ($expected as $key => $value) {
            foreach ($value as $dataKey => $dataValue) {
                $this->assertEquals($dataValue, $result['data'][$key][$dataKey]);
            }
        }
    }

    public function testGetListWithFilter()
    {
        $campaign = $this->getReference('Campaign.Campaign1');
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_marketing_activity_widget_marketing_activities_list',
                [
                    'entityClass' => TestActivity::class,
                    'entityId'    => $this->getReference(LoadTestEntityData::TEST_ENTITY_1)->getId(),
                    'filter'      => [
                        'campaigns' => ['value' => [$campaign->getId()]]
                    ]
                ]
            )
        );
        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertCount(1, $result['data']);
        $expected = [
            [
                'campaignName' => $campaign->getName(),
                'eventType' => 'Open',
                'editable' => false,
                'removable' => false,
            ],
        ];
        foreach ($expected as $key => $value) {
            foreach ($value as $dataKey => $dataValue) {
                $this->assertEquals($dataValue, $result['data'][$key][$dataKey]);
            }
        }
    }
}
