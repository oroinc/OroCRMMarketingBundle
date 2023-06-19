<?php

namespace Oro\Bundle\MarketingListBundle\Event;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched when marketing list is updated
 */
class UpdateMarketingListEvent extends Event
{
    /**
     * @var MarketingList[]
     */
    private $marketingLists;

    /**
     * @param MarketingList[] $marketingLists
     * @return UpdateMarketingListEvent
     */
    public function setMarketingLists(array $marketingLists): UpdateMarketingListEvent
    {
        $this->marketingLists = $marketingLists;

        return $this;
    }

    public function addMarketingList(MarketingList $marketingList): UpdateMarketingListEvent
    {
        $this->marketingLists[] = $marketingList;

        return $this;
    }

    /**
     * @return MarketingList[]
     */
    public function getMarketingLists(): array
    {
        return $this->marketingLists;
    }
}
