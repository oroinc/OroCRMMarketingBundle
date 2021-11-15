<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Bundle\MarketingListBundle\EventListener\UpdateMarketingListDemoDataFixturesListener;
use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Bundle\TestFrameworkBundle\Entity\Item;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListDemoDataFixturesListenerTest extends \PHPUnit\Framework\TestCase
{
    private const LISTENERS = [
        'test_listener_1',
        'test_listener_2',
    ];

    /** @var OptionalListenerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $listenerManager;

    /** @var EntityProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $entityProvider;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    /** @var MigrationDataFixturesEvent|\PHPUnit\Framework\MockObject\MockObject */
    private $event;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $entityRepository;

    /** @var UpdateMarketingListDemoDataFixturesListener */
    private $listener;

    protected function setUp(): void
    {
        $this->listenerManager = $this->createMock(OptionalListenerManager::class);
        $this->entityProvider = $this->createMock(EntityProvider::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityRepository = $this->createMock(EntityRepository::class);
        $this->event = $this->createMock(MigrationDataFixturesEvent::class);

        $this->listener = new UpdateMarketingListDemoDataFixturesListener(
            $this->listenerManager,
            $this->entityProvider,
            $this->dispatcher
        );
        $this->listener->disableListener(self::LISTENERS[0]);
        $this->listener->disableListener(self::LISTENERS[1]);
    }

    public function testOnPreLoad()
    {
        $this->event->expects($this->once())
            ->method('isDemoFixtures')
            ->willReturn(true);

        $this->listenerManager->expects($this->once())
            ->method('disableListeners')
            ->with(self::LISTENERS);

        $this->listener->onPreLoad($this->event);
    }

    public function testOnPreLoadWithNoDemoFixtures()
    {
        $this->event->expects($this->once())
            ->method('isDemoFixtures')
            ->willReturn(false);

        $this->listenerManager->expects($this->never())
            ->method('disableListeners');

        $this->listener->onPreLoad($this->event);
    }

    public function testOnPostLoad()
    {
        $marketingList = new MarketingList();
        ReflectionUtil::setId($marketingList, 1);

        $this->event->expects($this->once())
            ->method('isDemoFixtures')
            ->willReturn(true);

        $this->listenerManager->expects($this->once())
            ->method('enableListeners')
            ->with(self::LISTENERS);

        $this->event->expects($this->once())
            ->method('log')
            ->with('updating marketing lists');

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->with(false)
            ->willReturn([
                ['name' => Item::class],
            ]);

        $this->event->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->entityManager);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(MarketingList::class)
            ->willReturn($this->entityRepository);

        $this->entityRepository->expects($this->once())
            ->method('findBy')
            ->with([
                'type' => 'dynamic',
                'entity' => Item::class,
            ])
            ->willReturn([$marketingList]);

        $event = new UpdateMarketingListEvent();
        $event->setMarketingLists([$marketingList]);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($event, UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT);

        $this->listener->onPostLoad($this->event);
    }

    public function testOnPostLoadWithoutEntities()
    {
        $this->event->expects($this->once())
            ->method('isDemoFixtures')
            ->willReturn(true);

        $this->listenerManager->expects($this->once())
            ->method('enableListeners')
            ->with(self::LISTENERS);

        $this->event->expects($this->once())
            ->method('log')
            ->with('updating marketing lists');

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
            ->willReturn([]);

        $this->event->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->entityManager);

        $this->entityManager->expects($this->never())
            ->method($this->anything());

        $this->entityRepository->expects($this->never())
            ->method($this->anything());

        $this->dispatcher->expects($this->never())
            ->method($this->anything());

        $this->listener->onPostLoad($this->event);
    }

    public function testOnPostLoadWithNoDemoFixtures()
    {
        $this->event->expects($this->once())
            ->method('isDemoFixtures')
            ->willReturn(false);

        $this->listenerManager->expects($this->never())
            ->method($this->anything());

        $this->event->expects($this->never())
            ->method('log');

        $this->entityProvider->expects($this->never())
            ->method($this->anything());

        $this->entityManager->expects($this->never())
            ->method($this->anything());

        $this->entityRepository->expects($this->never())
            ->method($this->anything());

        $this->dispatcher->expects($this->never())
            ->method($this->anything());

        $this->listener->onPostLoad($this->event);
    }
}
