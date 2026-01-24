<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

/**
 * Defines the contract for entities that track state changes in marketing lists.
 */
interface MarketingListStateItemInterface
{
    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return MarketingListStateItemInterface
     */
    public function setEntityId($entityId);

    /**
     * @param MarketingList $marketingList
     *
     * @return MarketingListStateItemInterface
     */
    public function setMarketingList(MarketingList $marketingList);

    /**
     * @return MarketingList
     */
    public function getMarketingList();
}
