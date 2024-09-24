<?php

declare(strict_types=1);

namespace Oro\Bundle\MarketingListBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to update marketing lists by the specified entity class.
 */
class MarketingListUpdateTopic extends AbstractTopic
{
    public const CLASS_NAME = 'class';

    #[\Override]
    public static function getName(): string
    {
        return 'oro_marketing_list.message_queue.job.update_marketing_list';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Updates marketing lists by the specified entity class.';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->define(self::CLASS_NAME)
            ->required()
            ->allowedTypes('string')
            ->info('Fully qualifies entity class name.');
    }
}
