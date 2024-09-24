<?php

declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic aggregate visits tracking records.
 */
class TrackingAggregateVisitsTopic extends AbstractTopic
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro_tracking.aggregate_visits';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Aggregate visits tracking records.';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
