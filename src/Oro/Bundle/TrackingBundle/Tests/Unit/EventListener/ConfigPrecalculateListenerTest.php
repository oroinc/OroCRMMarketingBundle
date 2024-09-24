<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Async\Topic\TrackingAggregateVisitsTopic;
use Oro\Bundle\TrackingBundle\EventListener\ConfigPrecalculateListener;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class ConfigPrecalculateListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $producer;

    /** @var ConfigPrecalculateListener */
    private $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->producer = $this->createMock(MessageProducerInterface::class);

        $this->listener = new ConfigPrecalculateListener($this->producer);
    }

    public function testOnUpdateAfterNonGlobalScope(): void
    {
        $event = new ConfigUpdateEvent([], 'user', 1);

        $this->producer->expects(self::never())
            ->method(self::anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterNothingChanged(): void
    {
        $event = new ConfigUpdateEvent([], 'global', 0);

        $this->producer->expects(self::never())
            ->method(self::anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterRecalculationDisabled(): void
    {
        $event = new ConfigUpdateEvent(
            ['oro_tracking.precalculated_statistic_enabled' => ['old' => true, 'new' => false]],
            'global',
            0
        );

        $this->producer->expects(self::never())
            ->method(self::anything());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterTimezoneChanged(): void
    {
        $event = new ConfigUpdateEvent(
            ['oro_locale.timezone' => ['old' => 'old', 'new' => 'new']],
            'global',
            0
        );

        $this->producer->expects(self::once())
            ->method('send')
            ->with(TrackingAggregateVisitsTopic::getName());

        $this->listener->onUpdateAfter($event);
    }

    public function testOnUpdateAfterCalculationEnabled(): void
    {
        $event = new ConfigUpdateEvent(
            ['oro_tracking.precalculated_statistic_enabled' => ['old' => false, 'new' => true]],
            'global',
            0
        );

        $this->producer->expects(self::once())
            ->method('send')
            ->with(TrackingAggregateVisitsTopic::getName());

        $this->listener->onUpdateAfter($event);
    }
}
