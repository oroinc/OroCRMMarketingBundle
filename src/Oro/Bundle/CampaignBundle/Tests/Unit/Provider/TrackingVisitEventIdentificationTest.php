<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Provider\TrackingVisitEventIdentification;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;

class TrackingVisitEventIdentificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var TrackingVisitEventIdentification */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $this->provider = new TrackingVisitEventIdentification($this->doctrine);
    }

    public function testIsApplicable(): void
    {
        self::assertFalse($this->provider->isApplicable(new TrackingVisit()));
    }

    public function testGetIdentityTarget(): void
    {
        self::assertNull($this->provider->getIdentityTarget());
    }

    public function testGetEventTargets(): void
    {
        self::assertEquals([Campaign::class], $this->provider->getEventTargets());
    }

    public function testIsApplicableVisitEvent(): void
    {
        $webEvent = new TrackingEvent();
        $event = new TrackingVisitEvent();
        $event->setWebEvent($webEvent);

        self::assertFalse($this->provider->isApplicableVisitEvent($event));

        $webEvent->setCode('test');
        self::assertTrue($this->provider->isApplicableVisitEvent($event));
    }

    public function testProcessEventWhenCampaignFound(): void
    {
        $webEvent = new TrackingEvent();
        $webEvent->setCode('test');
        $event = new TrackingVisitEvent();
        $event->setWebEvent($webEvent);

        $campaign = new Campaign();

        $repo = $this->createMock(CampaignRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(Campaign::class)
            ->willReturn($repo);
        $repo->expects(self::once())
            ->method('findOneByCode')
            ->with('test')
            ->willReturn($campaign);

        self::assertSame([$campaign], $this->provider->processEvent($event));
    }

    public function testProcessEventWhenCampaignNotFound(): void
    {
        $webEvent = new TrackingEvent();
        $webEvent->setCode('test');
        $event = new TrackingVisitEvent();
        $event->setWebEvent($webEvent);

        $repo = $this->createMock(CampaignRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(Campaign::class)
            ->willReturn($repo);
        $repo->expects(self::once())
            ->method('findOneByCode')
            ->with('test')
            ->willReturn(null);

        self::assertSame([], $this->provider->processEvent($event));
    }
}
