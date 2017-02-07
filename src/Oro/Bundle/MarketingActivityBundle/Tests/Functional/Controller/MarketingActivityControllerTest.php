<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class MarketingActivityControllerTest extends WebTestCase
{
    public function setUp()
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
            "Send" => '1',
            "Open" => '1',
            "Click" => '3',
            "Soft Bounce" => '1',
            "Hard Bounce" => '1',
            "Unsubscribe" => '2',
        ];
        foreach ($expectedTypes as $type => $value) {
            $typeXpath = '//div[contains(@class, "control-group")]/label[text() = "' . $type . '"]/'
                . 'following-sibling::div/div[contains(@class, "control-label")]';
            $this->assertEquals($value, $crawler->filterXPath($typeXpath)->text());
        }
    }
}
