<?php

namespace Oro\Bundle\TrackingBundle\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Async\Topics;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Util\JSON;

class ConfigPrecalculateListener
{
    /**
     * @var MessageProducerInterface
     */
    private $producer;

    public function __construct(MessageProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    public function onUpdateAfter(ConfigUpdateEvent $event)
    {
        if (!$event->getScope() === 'global') {
            return;
        }

        $statisticToggleKey = 'oro_tracking.precalculated_statistic_enabled';
        if (($event->isChanged($statisticToggleKey) && $event->getNewValue($statisticToggleKey))
            || $event->isChanged('oro_locale.timezone')
        ) {
            $this->producer->send(Topics::AGGREGATE_VISITS, JSON::encode(''));
        }
    }
}
