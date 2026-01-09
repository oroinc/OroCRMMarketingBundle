<?php

namespace Oro\Bundle\CampaignBundle\Model;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;

/**
 * Builds and configures email campaign senders with campaign-specific settings.
 */
class EmailCampaignSenderBuilder
{
    /**
     * @var EmailCampaignSender
     */
    protected $campaignSender;

    public function __construct(EmailCampaignSender $campaignSender)
    {
        $this->campaignSender = $campaignSender;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return EmailCampaignSender
     */
    public function getSender(EmailCampaign $emailCampaign)
    {
        $this->campaignSender->setEmailCampaign($emailCampaign);

        return $this->campaignSender;
    }
}
