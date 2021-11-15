<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TrackingBundle\Async\AggregateTrackingVisitsProcessor;
use Oro\Bundle\TrackingBundle\Async\Topics;
use Oro\Bundle\TrackingBundle\Tools\UniqueTrackingVisitDumper;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

class AggregateTrackingVisitsProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var UniqueTrackingVisitDumper|\PHPUnit\Framework\MockObject\MockObject */
    private $trackingVisitDumper;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var AggregateTrackingVisitsProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->trackingVisitDumper = $this->createMock(UniqueTrackingVisitDumper::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new AggregateTrackingVisitsProcessor(
            $this->trackingVisitDumper,
            $this->configManager,
            $this->logger
        );
    }

    public function testProcessException()
    {
        $message = $this->createMock(MessageInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);

        $exception = new \Exception('Test');
        $this->trackingVisitDumper->expects($this->once())
            ->method('refreshAggregatedData')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected exception occurred during Tracking Visit aggregation', ['exception' => $exception]);

        $this->assertSame(MessageProcessorInterface::REJECT, $this->processor->process($message, $session));
    }

    public function testProcess()
    {
        $message = $this->createMock(MessageInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);

        $this->trackingVisitDumper->expects($this->once())
            ->method('refreshAggregatedData');

        $this->logger->expects($this->never())
            ->method($this->anything());

        $this->assertSame(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }

    public function testProcessDisabled()
    {
        $message = $this->createMock(MessageInterface::class);
        $session = $this->createMock(SessionInterface::class);

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(false);

        $this->trackingVisitDumper->expects($this->never())
            ->method('refreshAggregatedData');

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Tracking Visit aggregation disabled');

        $this->assertSame(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }

    public function testGetSubscribedTopics()
    {
        $this->assertSame([Topics::AGGREGATE_VISITS], AggregateTrackingVisitsProcessor::getSubscribedTopics());
    }
}
