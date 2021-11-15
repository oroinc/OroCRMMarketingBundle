<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Provider\TrackingVisitEventIdentification;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;

class TrackingVisitEventIdentificationTest extends \PHPUnit\Framework\TestCase
{
    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var TrackingVisitEventIdentification */
    private $provider;

    protected function setUp(): void
    {
        $this->em = $this->createMock(ObjectManager::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($this->em);

        $this->provider = new TrackingVisitEventIdentification($doctrine);
    }

    public function testIsApplicable()
    {
        $this->assertFalse($this->provider->isApplicable(new TrackingVisit()));
    }

    public function testGetIdentityTarget()
    {
        $this->assertNull($this->provider->getIdentityTarget());
    }

    public function testGetEventTargets()
    {
        $this->assertEquals(
            [
                Campaign::class
            ],
            $this->provider->getEventTargets()
        );
    }

    public function testIsApplicableVisitEvent()
    {
        $event = new TrackingVisitEvent();
        $webEvent = new TrackingEvent();
        $event->setWebEvent($webEvent);
        $this->assertFalse($this->provider->isApplicableVisitEvent($event));
        $webEvent->setCode('test');
        $this->assertTrue($this->provider->isApplicableVisitEvent($event));
    }

    /**
     * @dataProvider processData
     */
    public function testProcessEvent(bool $isFind)
    {
        $event = new TrackingVisitEvent();
        $webEvent = new TrackingEvent();
        $webEvent->setCode('test');
        $event->setWebEvent($webEvent);

        $testResult = new \stdClass();

        $repo = $this->createMock(CampaignRepository::class);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(Campaign::class)
            ->willReturn($repo);
        $repo->expects($this->once())
            ->method('findOneByCode')
            ->with('test')
            ->willReturn($isFind ? $testResult : null);

        $this->assertEquals($isFind ? [$testResult] : [], $this->provider->processEvent($event));
    }

    public function processData(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
