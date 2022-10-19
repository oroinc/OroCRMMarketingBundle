<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Async;

use Oro\Bundle\CampaignBundle\Async\Topic\SendEmailCampaignTopic;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadEmailCampaignData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

/**
 * @dbIsolationPerTest
 */
class EmailCampaignSendProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([LoadEmailCampaignData::class]);
    }

    public function testProcessEmailCampaignNotFound(): void
    {
        $sentMessage = self::sendMessage(
            SendEmailCampaignTopic::getName(),
            ['email_campaign' => PHP_INT_MAX]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::REJECT, $sentMessage);
        self::assertProcessedMessageProcessor('oro_campaign.async.email_campaign_send_processor', $sentMessage);
        self::assertTrue(
            self::getLoggerTestHandler()->hasNotice(
                sprintf('Email campaign with id %d was not found', PHP_INT_MAX)
            )
        );
    }

    public function testProcess(): void
    {
        $sentMessage = self::sendMessage(
            SendEmailCampaignTopic::getName(),
            ['email_campaign' => $this->getReference('CampaignBundle.Campaign1')->getId()]
        );
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_campaign.async.email_campaign_send_processor', $sentMessage);
    }
}
