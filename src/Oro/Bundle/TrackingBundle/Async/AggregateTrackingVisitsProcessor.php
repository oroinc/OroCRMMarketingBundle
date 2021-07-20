<?php

namespace Oro\Bundle\TrackingBundle\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TrackingBundle\Tools\UniqueTrackingVisitDumper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

class AggregateTrackingVisitsProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var UniqueTrackingVisitDumper
     */
    private $trackingVisitDumper;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UniqueTrackingVisitDumper $trackingVisitDumper,
        ConfigManager $configManager,
        LoggerInterface $logger
    ) {
        $this->trackingVisitDumper = $trackingVisitDumper;
        $this->configManager = $configManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        if (!$this->configManager->get('oro_tracking.precalculated_statistic_enabled')) {
            $this->logger->info('Tracking Visit aggregation disabled');
            return self::ACK;
        }

        try {
            $this->trackingVisitDumper->refreshAggregatedData();
        } catch (\Exception $e) {
            $this->logger->error(
                'Unexpected exception occurred during Tracking Visit aggregation',
                ['exception' => $e]
            );

            return self::REJECT;
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::AGGREGATE_VISITS];
    }
}
