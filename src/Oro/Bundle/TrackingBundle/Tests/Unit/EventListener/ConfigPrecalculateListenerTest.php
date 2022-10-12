<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Async\Topic\TrackingAggregateVisitsTopic;
use Oro\Bundle\TrackingBundle\EventListener\ConfigPrecalculateListener;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class ConfigPrecalculateListenerTest extends \PHPUnit\Framework\TestCase
{
    private MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject $producer;

    private ConfigPrecalculateListener $listener;

    protected function setUp(): void
    {
        $this->producer = $this->createMock(MessageProducerInterface::class);

        $this->listener = new ConfigPrecalculateListener($this->producer);
    }

    public function testOnUpdateAfterNonGlobalScope(): void
    {
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects(self::once())
            ->method('getScope')
            ->willReturn('user');

        $this->producer->expects(self::never())
            ->method(self::anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterNothingChanged(): void
    {
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects(self::once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects(self::exactly(2))
            ->method('isChanged')
            ->withConsecutive(['oro_tracking.precalculated_statistic_enabled'], ['oro_locale.timezone'])
            ->willReturn(false);

        $this->producer->expects(self::never())
            ->method(self::anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterRecalculationDisabled(): void
    {
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects(self::once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects(self::exactly(2))
            ->method('isChanged')
            ->withConsecutive(['oro_tracking.precalculated_statistic_enabled'], ['oro_locale.timezone'])
            ->willReturn(true, false);
        $event->expects(self::once())
            ->method('getNewValue')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(false);

        $this->producer->expects(self::never())
            ->method(self::anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterTimezoneChanged(): void
    {
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects(self::once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects(self::exactly(2))
            ->method('isChanged')
            ->withConsecutive(['oro_tracking.precalculated_statistic_enabled'], ['oro_locale.timezone'])
            ->willReturnOnConsecutiveCalls(false, true);

        $this->producer->expects(self::once())
            ->method('send')
            ->with(TrackingAggregateVisitsTopic::getName());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterCalculationEnabled(): void
    {
        $event = $this->createMock(ConfigUpdateEvent::class);
        $event->expects(self::once())
            ->method('getScope')
            ->willReturn('global');
        $event->expects(self::once())
            ->method('isChanged')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);
        $event->expects(self::once())
            ->method('getNewValue')
            ->with('oro_tracking.precalculated_statistic_enabled')
            ->willReturn(true);

        $this->producer->expects(self::once())
            ->method('send')
            ->with(TrackingAggregateVisitsTopic::getName());

        $this->listener->onUpdateAfter($event);
    }
}
