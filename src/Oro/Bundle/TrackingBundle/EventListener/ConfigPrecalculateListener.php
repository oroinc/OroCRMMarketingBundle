<?php

namespace Oro\Bundle\TrackingBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Async\Topic\TrackingAggregateVisitsTopic;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

/**
 * Initiates the visits tracking aggregation by sending the {@see TrackingAggregateVisitsTopic} MQ message.
 */
class ConfigPrecalculateListener
{
    private MessageProducerInterface $producer;

    public function __construct(MessageProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function onUpdateAfter(ConfigUpdateEvent $event): void
    {
        if (!$event->getScope() === 'global') {
            return;
        }

        $statisticToggleKey = 'oro_tracking.precalculated_statistic_enabled';
        if (($event->isChanged($statisticToggleKey) && $event->getNewValue($statisticToggleKey))
            || $event->isChanged('oro_locale.timezone')
        ) {
            $this->producer->send(TrackingAggregateVisitsTopic::getName(), []);
        }
    }
}
