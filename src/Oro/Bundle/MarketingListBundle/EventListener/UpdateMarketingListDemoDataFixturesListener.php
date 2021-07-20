<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;
use Oro\Bundle\PlatformBundle\EventListener\AbstractDemoDataFixturesListener;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListDemoDataFixturesListener extends AbstractDemoDataFixturesListener
{
    /** @var EntityProvider */
    protected $entityProvider;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    public function __construct(
        OptionalListenerManager $listenerManager,
        EntityProvider $entityProvider,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($listenerManager);

        $this->entityProvider = $entityProvider;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    protected function afterEnableListeners(MigrationDataFixturesEvent $event)
    {
        $event->log('updating marketing lists');

        $this->updateMarketingList($event->getObjectManager());
    }

    protected function updateMarketingList(ObjectManager $manager)
    {
        $classes = array_map(
            function ($entity) {
                return $entity['name'];
            },
            $this->entityProvider->getEntities(false)
        );

        foreach ($classes as $class) {
            $marketingLists = $this->findMarketingLists($manager, $class);
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

        $this->dispatcher->dispatch($event, UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT);
    }

    /**
     * @param ObjectManager $manager
     * @param string $class
     * @return MarketingList[]
     */
    protected function findMarketingLists(ObjectManager $manager, $class)
    {
        return $manager->getRepository(MarketingList::class)
            ->findBy([
                'type' => 'dynamic',
                'entity' => $class,
            ]);
    }
}
