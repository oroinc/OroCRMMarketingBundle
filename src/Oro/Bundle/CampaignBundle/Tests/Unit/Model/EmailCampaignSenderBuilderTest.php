<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Model;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;

class EmailCampaignSenderBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $campaignSender;

    /**
     * @var EmailCampaignSenderBuilder
     */
    protected $factory;

    protected function setUp(): void
    {
        $this->campaignSender = $this
            ->getMockBuilder('Oro\Bundle\CampaignBundle\Model\EmailCampaignSender')
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new EmailCampaignSenderBuilder($this->campaignSender);
    }

    public function testGetSender()
    {
        $emailCampaign = new EmailCampaign();

        $this->campaignSender
            ->expects($this->once())
            ->method('setEmailCampaign')
            ->with($this->equalTo($emailCampaign));

        $this->assertEquals($this->campaignSender, $this->factory->getSender($emailCampaign));
    }
}
