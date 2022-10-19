<?php

namespace Oro\Bundle\CampaignBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to send email campaign
 */
class SendEmailCampaignTopic extends AbstractTopic
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
}
