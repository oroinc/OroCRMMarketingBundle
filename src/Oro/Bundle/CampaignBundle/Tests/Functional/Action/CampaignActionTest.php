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
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => 'oro_campaign_delete',
                    'entityId' => $campaign->getId(),
                    'entityClass' => Campaign::class,
                ]
            ),
            [],
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);
    }
}
