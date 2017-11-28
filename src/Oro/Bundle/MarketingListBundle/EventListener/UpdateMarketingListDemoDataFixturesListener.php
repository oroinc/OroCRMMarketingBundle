<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListDemoDataFixturesListener
{
    const LISTENERS = [
        'oro_marketing_list.event_listener.on_entity_change',
    ];

    /** @var OptionalListenerManager */
    protected $listenerManager;

    /** @var EntityProvider */
    protected $entityProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param OptionalListenerManager $listenerManager
     * @param EntityProvider $entityProvider
     * @param DoctrineHelper $doctrineHelper
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        OptionalListenerManager $listenerManager,
        EntityProvider $entityProvider,
        DoctrineHelper $doctrineHelper,
        EventDispatcherInterface $dispatcher
    ) {
        $this->listenerManager = $listenerManager;
        $this->entityProvider = $entityProvider;
        $this->doctrineHelper = $doctrineHelper;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param MigrationDataFixturesEvent $event
     */
    public function onPreLoad(MigrationDataFixturesEvent $event)
    {
        if (!$event->isDemoFixtures()) {
            return;
        }

        $this->listenerManager->disableListeners(self::LISTENERS);
    }

    /**
     * @param MigrationDataFixturesEvent $event
     */
    public function onPostLoad(MigrationDataFixturesEvent $event)
    {
        if (!$event->isDemoFixtures()) {
            return;
        }

        $this->listenerManager->enableListeners(self::LISTENERS);

        $event->log('updating marketing lists');

        $this->updateMarketingList();
    }

    protected function updateMarketingList()
    {
        $classes = array_map(
            function ($entity) {
                return $entity['name'];
            },
            $this->entityProvider->getEntities(false)
        );

        foreach ($classes as $class) {
            $marketingLists = $this->findMarketingLists($class);
            if (count($marketingLists)) {
                $this->dispatch($marketingLists);
            }
        }
    }

    /**
     * @param MarketingList[] $marketingLists
     */
    protected function dispatch(array $marketingLists)
    {
        $event = new UpdateMarketingListEvent();
        $event->setMarketingLists($marketingLists);

        $this->dispatcher->dispatch(UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT, $event);
    }

    /**
     * @param string $class
     * @return MarketingList[]
     */
    protected function findMarketingLists($class)
    {
        return $this->doctrineHelper
            ->getEntityManager(MarketingList::class)
            ->getRepository(MarketingList::class)
            ->findBy([
                'type' => 'dynamic',
                'entity' => $class,
            ]);
    }
}
