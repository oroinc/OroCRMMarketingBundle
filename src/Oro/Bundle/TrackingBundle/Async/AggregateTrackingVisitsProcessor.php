<?php

namespace Oro\Bundle\TrackingBundle\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TrackingBundle\Async\Topic\TrackingAggregateVisitsTopic;
use Oro\Bundle\TrackingBundle\Tools\UniqueTrackingVisitDumper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Aggregates visits tracking records if it is enabled in system configuration.
 */
class AggregateTrackingVisitsProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    private UniqueTrackingVisitDumper $trackingVisitDumper;

    private ConfigManager $configManager;

    public function __construct(
        UniqueTrackingVisitDumper $trackingVisitDumper,
        ConfigManager $configManager
    ) {
        $this->trackingVisitDumper = $trackingVisitDumper;
        $this->configManager = $configManager;

        $this->logger = new NullLogger();
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        if (!$this->configManager->get('oro_tracking.precalculated_statistic_enabled')) {
            $this->logger->info('Tracking Visit aggregation disabled');
            return self::ACK;
        }

        $this->trackingVisitDumper->refreshAggregatedData();

        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [TrackingAggregateVisitsTopic::getName()];
    }
}
