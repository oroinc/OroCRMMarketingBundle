<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Model;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSender;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;

class EmailCampaignSenderBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailCampaignSender|\PHPUnit\Framework\MockObject\MockObject */
    private $campaignSender;

    /** @var EmailCampaignSenderBuilder */
    private $factory;

    protected function setUp(): void
    {
        $this->campaignSender = $this->createMock(EmailCampaignSender::class);

        $this->factory = new EmailCampaignSenderBuilder($this->campaignSender);
    }

    public function testGetSender()
    {
        $emailCampaign = new EmailCampaign();

        $this->campaignSender->expects($this->once())
            ->method('setEmailCampaign')
            ->with($this->equalTo($emailCampaign));

        $this->assertEquals($this->campaignSender, $this->factory->getSender($emailCampaign));
    }
}
