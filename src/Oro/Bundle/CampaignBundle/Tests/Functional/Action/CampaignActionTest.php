<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Action;

use Oro\Bundle\ActionBundle\Tests\Functional\OperationAwareTestTrait;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CampaignActionTest extends WebTestCase
{
    use OperationAwareTestTrait;

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadCampaignData::class]);
    }

    public function testDelete()
    {
        /** @var Campaign campaign */
        $campaign = $this->getReference('Campaign.Campaign1');
        $campaignId = $campaign->getId();

        $operationName = 'oro_campaign_delete';
        $entityClass = Campaign::class;
        $this->client->request(
            'POST',
            $this->getUrl(
                'oro_action_operation_execute',
                [
                    'operationName' => $operationName,
                    'entityId' => $campaignId,
                    'entityClass' => $entityClass,
                ]
            ),
            $this->getOperationExecuteParams($operationName, $campaignId, $entityClass),
            [],
            ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
        );

        $this->assertJsonResponseStatusCodeEquals($this->client->getResponse(), 200);

        self::getContainer()->get('doctrine')->getManagerForClass($entityClass)->clear();

        $removedCampaign = self::getContainer()->get('doctrine')->getRepository(Campaign::class)
            ->find($campaignId);

        self::assertNull($removedCampaign);
    }
}
