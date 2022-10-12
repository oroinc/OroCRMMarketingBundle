<?php

declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Async;

use Oro\Bundle\ConfigBundle\Tests\Functional\Traits\ConfigManagerAwareTestTrait;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TrackingBundle\Async\Topic\TrackingAggregateVisitsTopic;
use Oro\Bundle\TrackingBundle\Entity\UniqueTrackingVisit;
use Oro\Bundle\TrackingBundle\Tests\Functional\DataFixtures\LoadTrackingVisits;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;

class AggregateTrackingVisitsProcessorTest extends WebTestCase
{
    use MessageQueueExtension;
    use ConfigManagerAwareTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
    }

    protected function tearDown(): void
    {
        self::toggleConfig(true);

        parent::tearDown();
    }

    public function testProcessWhenNoData(): void
    {
        $uniqueTrackingVisitRepo = self::getContainer()->get('doctrine')->getRepository(UniqueTrackingVisit::class);
        self::assertEmpty($uniqueTrackingVisitRepo->findAll());

        $sentMessage = self::sendMessage(TrackingAggregateVisitsTopic::getName(), []);
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_tracking.async.aggregate_tracking_visits_processor', $sentMessage);

        self::assertEmpty($uniqueTrackingVisitRepo->findAll());
    }

    public function testProcessWhenDisabled(): void
    {
        self::toggleConfig(false);

        $uniqueTrackingVisitRepo = self::getContainer()->get('doctrine')->getRepository(UniqueTrackingVisit::class);
        self::assertEmpty($uniqueTrackingVisitRepo->findAll());

        $sentMessage = self::sendMessage(TrackingAggregateVisitsTopic::getName(), []);
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_tracking.async.aggregate_tracking_visits_processor', $sentMessage);

        self::assertEmpty($uniqueTrackingVisitRepo->findAll());
        self::assertTrue(self::getLoggerTestHandler()->hasInfo('Tracking Visit aggregation disabled'));
    }

    public function testProcess(): void
    {
        // Disables {@see \Oro\Bundle\TrackingBundle\EventListener\ConfigPrecalculateListener} during fixtures loading.
        self::toggleConfig(false);
        $this->loadFixtures([LoadTrackingVisits::class]);
        self::toggleConfig(true);

        $uniqueTrackingVisitRepo = self::getContainer()->get('doctrine')->getRepository(UniqueTrackingVisit::class);
        self::assertEmpty($uniqueTrackingVisitRepo->findAll());

        $sentMessage = self::sendMessage(TrackingAggregateVisitsTopic::getName(), []);
        self::consumeMessage($sentMessage);

        self::assertProcessedMessageStatus(MessageProcessorInterface::ACK, $sentMessage);
        self::assertProcessedMessageProcessor('oro_tracking.async.aggregate_tracking_visits_processor', $sentMessage);

        self::assertNotEmpty($uniqueTrackingVisitRepo->findAll());
    }

    private static function toggleConfig(bool $value): void
    {
        self::getConfigManager()->set('oro_tracking.precalculated_statistic_enabled', $value);
        self::getConfigManager()->flush();
    }
}
