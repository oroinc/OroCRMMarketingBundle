<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * This listener invalidates marketing list cache when marketing list entities set is changed.
 */
class MarketingListEntityListener
{
    private CacheInterface $cacheProvider;

    public function __construct(CacheInterface $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function postUpdate(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->clear();
    }

    public function postPersist(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->clear();
    }

    public function postRemove(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->clear();
    }
}
