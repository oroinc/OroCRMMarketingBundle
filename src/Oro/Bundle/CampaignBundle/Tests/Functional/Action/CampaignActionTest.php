<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Action;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CampaignActionTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            [
                LoadCampaignData::class,
            ]
        );
    }

    public function testDelete()
    {
        /** @var Campaign campaign */
        $campaign = $this->getReference('Campaign.Campaign1');
        $campaignId = $campaign->getId();

        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => 'oro_campaign_delete',
                    'entityId' => $campaignId,
                    'entityClass' => Campaign::class,
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        static::getContainer()->get('doctrine')->getManagerForClass(Campaign::class)->clear();

        $removedCampaign = static::getContainer()
            ->get('doctrine')
            ->getRepository('OroCampaignBundle:Campaign')
            ->find($campaignId);

        static::assertNull($removedCampaign);
    }
}
