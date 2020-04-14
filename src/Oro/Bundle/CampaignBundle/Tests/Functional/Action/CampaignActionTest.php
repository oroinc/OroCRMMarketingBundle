<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Action;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CampaignActionTest extends WebTestCase
{
    protected function setUp(): void
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

        $removedCampaign = static::getContainer()
            ->get('doctrine')
            ->getRepository('OroCampaignBundle:Campaign')
            ->find($campaignId);

        static::assertNull($removedCampaign);
    }

    /**
     * @param $operationName
     * @param $entityId
     * @param $entityClass
     *
     * @return array
     */
    protected function getOperationExecuteParams($operationName, $entityId, $entityClass)
    {
        $actionContext = [
            'entityId'    => $entityId,
            'entityClass' => $entityClass
        ];
        $container = self::getContainer();
        $operation = $container->get('oro_action.operation_registry')->findByName($operationName);
        $actionData = $container->get('oro_action.helper.context')->getActionData($actionContext);

        $tokenData = $container
            ->get('oro_action.operation.execution.form_provider')
            ->createTokenData($operation, $actionData);
        $container->get('session')->save();

        return $tokenData;
    }
}
