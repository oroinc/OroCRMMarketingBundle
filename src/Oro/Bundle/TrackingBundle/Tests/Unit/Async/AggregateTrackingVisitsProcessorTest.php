<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TrackingBundle\Async\AggregateTrackingVisitsProcessor;
use Oro\Bundle\TrackingBundle\Async\Topics;
use Oro\Bundle\TrackingBundle\Tools\UniqueTrackingVisitDumper;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

class AggregateTrackingVisitsProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UniqueTrackingVisitDumper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $trackingVisitDumper;

    /**
     * @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configManager;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var AggregateTrackingVisitsProcessor
     */
    private $processor;

    protected function setUp()
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
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
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

        $this->assertSame(AggregateTrackingVisitsProcessor::REJECT, $this->processor->process($message, $session));
    }

    public function testProcess()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
        $session = $this->createMock(SessionInterface::class);


        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);

        $this->trackingVisitDumper->expects($this->once())
            ->method('refreshAggregatedData');

        $this->logger->expects($this->never())
            ->method($this->anything());

        $this->assertSame(AggregateTrackingVisitsProcessor::ACK, $this->processor->process($message, $session));
    }

    public function testProcessDisabled()
    {
        /** @var MessageInterface|\PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(MessageInterface::class);
        /** @var SessionInterface|\PHPUnit_Framework_MockObject_MockObject $session */
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

        $this->assertSame(AggregateTrackingVisitsProcessor::ACK, $this->processor->process($message, $session));
    }

    public function testGetSubscribedTopics()
    {
        $this->assertSame([Topics::AGGREGATE_VISITS], AggregateTrackingVisitsProcessor::getSubscribedTopics());
    }
}
