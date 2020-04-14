<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Async\Topics;
use Oro\Bundle\TrackingBundle\EventListener\ConfigPrecalculateListener;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class ConfigPrecalculateListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $producer;

    /**
     * @var ConfigPrecalculateListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->producer = $this->createMock(MessageProducerInterface::class);

        $this->listener = new ConfigPrecalculateListener($this->producer);
    }

    public function testOnUpdateAfterNonGlobalScope()
    {
        /** @var ConfigUpdateEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects($this->once())
            ->method('getScope')
            ->willReturn('user');

        $this->producer->expects($this->never())
            ->method($this->anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterNothingChanged()
    {
        /** @var ConfigUpdateEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects($this->once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects($this->exactly(2))
            ->method('isChanged')
            ->withConsecutive(['oro_tracking.precalculated_statistic_enabled'], ['oro_locale.timezone'])
            ->willReturn(false);

        $this->producer->expects($this->never())
            ->method($this->anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterRecalculationDisabled()
    {
        /** @var ConfigUpdateEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects($this->once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects($this->exactly(2))
            ->method('isChanged')
            ->withConsecutive(['oro_tracking.precalculated_statistic_enabled'], ['oro_locale.timezone'])
            ->willReturn(true, false);
        $event->expects($this->once())
            ->method('getNewValue')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(false);

        $this->producer->expects($this->never())
            ->method($this->anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterTimezoneChanged()
    {
        /** @var ConfigUpdateEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects($this->once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects($this->exactly(2))
            ->method('isChanged')
            ->withConsecutive(['oro_tracking.precalculated_statistic_enabled'], ['oro_locale.timezone'])
            ->willReturnOnConsecutiveCalls(false, true);

        $this->producer->expects($this->once())
            ->method('send')
            ->with(Topics::AGGREGATE_VISITS);

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterCalculationEnabled()
    {
        /** @var ConfigUpdateEvent|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects($this->once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects($this->once())
            ->method('isChanged')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);
        $event->expects($this->once())
            ->method('getNewValue')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);

        $this->producer->expects($this->once())
            ->method('send')
            ->with(Topics::AGGREGATE_VISITS);

        $this->listener->onUpdateAfter($event);
    }
}
