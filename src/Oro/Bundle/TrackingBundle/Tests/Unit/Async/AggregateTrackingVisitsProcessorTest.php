<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Bundle\TrackingBundle\Async\AggregateTrackingVisitsProcessor;
use Oro\Bundle\TrackingBundle\Async\Topic\TrackingAggregateVisitsTopic;
use Oro\Bundle\TrackingBundle\Tools\UniqueTrackingVisitDumper;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

class AggregateTrackingVisitsProcessorTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private UniqueTrackingVisitDumper|\PHPUnit\Framework\MockObject\MockObject $trackingVisitDumper;

    private ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager;

    private AggregateTrackingVisitsProcessor $processor;

    protected function setUp(): void
    {
        $this->trackingVisitDumper = $this->createMock(UniqueTrackingVisitDumper::class);
        $this->configManager = $this->createMock(ConfigManager::class);

        $this->processor = new AggregateTrackingVisitsProcessor(
            $this->trackingVisitDumper,
            $this->configManager
        );
        $this->setUpLoggerMock($this->processor);
    }

    public function testProcess(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);

        $this->trackingVisitDumper->expects(self::once())
            ->method('refreshAggregatedData');

        $this->loggerMock->expects(self::never())
            ->method(self::anything());

        self::assertSame(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }

    public function testProcessDisabled(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $this->configManager->expects(self::once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(false);

        $this->trackingVisitDumper->expects(self::never())
            ->method('refreshAggregatedData');

        $this->loggerMock->expects(self::once())
            ->method('info')
            ->with('Tracking Visit aggregation disabled');

        self::assertSame(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }

    public function testGetSubscribedTopics(): void
    {
        self::assertSame(
            [TrackingAggregateVisitsTopic::getName()],
            AggregateTrackingVisitsProcessor::getSubscribedTopics()
        );
    }
}
