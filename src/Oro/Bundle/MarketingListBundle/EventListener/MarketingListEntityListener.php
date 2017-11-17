<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * This listener invalidates marketing list cache when marketing list entities set is changed.
 */
class MarketingListEntityListener
{
    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @param CacheProvider $cacheProvider
     */
    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @param MarketingList $marketingList
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->deleteAll();
    }

    /**
     * @param MarketingList $marketingList
     * @param LifecycleEventArgs $event
     */
    public function postPersist(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->deleteAll();
    }

    /**
     * @param MarketingList $marketingList
     * @param LifecycleEventArgs $event
     */
    public function postRemove(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->deleteAll();
    }
}
