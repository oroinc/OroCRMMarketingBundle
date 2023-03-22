<?php

namespace Oro\Bundle\CampaignBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to send email campaign
 */
class SendEmailCampaignTopic extends AbstractTopic implements JobAwareTopicInterface
{
    public static function getName(): string
    {
        return 'oro_campaign.email_campaign.send';
    }

    public static function getDescription(): string
    {
        return 'Sends email campaign.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('email_campaign')
            ->addAllowedTypes('email_campaign', 'int');
    }

    public function createJobName($messageBody): string
    {
        $emailCampaignId = $messageBody['email_campaign'];

        return SendEmailCampaignTopic::getName() . ':' . $emailCampaignId;
    }
}
