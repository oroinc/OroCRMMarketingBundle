<?php

namespace Oro\Bundle\MarketingListBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

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
    public function setMarketingLists(array $marketingLists) : UpdateMarketingListEvent
    {
        $this->marketingLists = $marketingLists;

        return $this;
    }

    /**
     * @param MarketingList $marketingList
     * @return UpdateMarketingListEvent
     */
    public function addMarketingList(MarketingList $marketingList) : UpdateMarketingListEvent
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
