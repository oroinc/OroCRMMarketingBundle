<?php

declare(strict_types=1);

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Controller;

use Oro\Bundle\CampaignBundle\Async\Topic\SendEmailCampaignTopic;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadEmailCampaignData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueAssertTrait;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * Tests sending an email campaign manually from the back-office.
 *
 * @dbIsolationPerTest
 */
final class EmailCampaignControllerTest extends WebTestCase
{
    use MessageQueueAssertTrait;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $this->loadFixtures([LoadEmailCampaignData::class]);
    }

    public function testSendWithoutCsrfToken(): void
    {
        self::clearMessageCollector();
        $emailCampaign = $this->getReference('CampaignBundle.Campaign1');

        $this->client->request('GET', $this->getUrl('oro_email_campaign_send', ['id' => $emailCampaign->getId()]));

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 403);
        $this->getContainer()->get('doctrine')->getManagerForClass(EmailCampaign::class)->refresh($emailCampaign);
        self::assertFalse($emailCampaign->isSent());
        self::assertMessagesEmpty(SendEmailCampaignTopic::getName());
    }

    public function testSendWithCsrfToken(): void
    {
        self::clearMessageCollector();
        $emailCampaign = $this->getReference('CampaignBundle.Campaign1');

        $this->ajaxRequest('GET', $this->getUrl('oro_email_campaign_send', ['id' => $emailCampaign->getId()]));

        self::assertResponseStatusCodeEquals($this->client->getResponse(), 302);
        $this->getContainer()->get('doctrine')->getManagerForClass(EmailCampaign::class)->refresh($emailCampaign);
        self::assertTrue($emailCampaign->isSent());
        self::assertMessageSent(SendEmailCampaignTopic::getName(), ['email_campaign' => $emailCampaign->getId()]);
    }
}
