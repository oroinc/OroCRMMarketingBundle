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

    public function __construct(CacheProvider $cacheProvider)
    {
        $this->cacheProvider = $cacheProvider;
    }

    public function postUpdate(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->deleteAll();
    }

    public function postPersist(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->deleteAll();
    }

    public function postRemove(MarketingList $marketingList, LifecycleEventArgs $event)
    {
        $this->cacheProvider->deleteAll();
    }
}
