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
    #[\Override]
    public static function getName(): string
    {
        return 'oro_campaign.email_campaign.send';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Sends email campaign.';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('email_campaign')
            ->addAllowedTypes('email_campaign', 'int');
    }

    #[\Override]
    public function createJobName($messageBody): string
    {
        $emailCampaignId = $messageBody['email_campaign'];

        return SendEmailCampaignTopic::getName() . ':' . $emailCampaignId;
    }
}
