<?php

namespace Oro\Bundle\MarketingListBundle\Model;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;

/**
 * A service for contacting Marketing List item.
 */
class MarketingListItemConnector
{
    private ManagerRegistry $doctrine;
    private DoctrineHelper $doctrineHelper;

    public function __construct(ManagerRegistry $doctrine, DoctrineHelper $doctrineHelper)
    {
        $this->doctrine = $doctrine;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param MarketingList $marketingList
     * @param int $entityId
     * @return MarketingListItem
     */
    public function getMarketingListItem(MarketingList $marketingList, $entityId)
    {
        $marketingListItemRepository = $this->doctrine->getRepository(MarketingListItem::class);
        $marketingListItem = $marketingListItemRepository->findOneBy(
            ['marketingList' => $marketingList, 'entityId' => $entityId]
        );

        if (!$marketingListItem) {
            $marketingListItem = new MarketingListItem();
            $marketingListItem->setMarketingList($marketingList)
                ->setEntityId($entityId);

            $manager = $this->doctrine->getManagerForClass(MarketingListItem::class);
            $manager->persist($marketingListItem);
        }

        return $marketingListItem;
    }

    /**
     * @param MarketingList $marketingList
     * @param int $entityId
     * @return MarketingListItem
     */
    public function contact(MarketingList $marketingList, $entityId)
    {
        $marketingListItem = $this->getMarketingListItem($marketingList, $entityId);
        $marketingListItem->contact();

        return $marketingListItem;
    }

    /**
     * @param MarketingList $marketingList
     * @param array $result
     * @return MarketingListItem
     */
    public function contactResultRow(MarketingList $marketingList, array $result)
    {
        $idName = $this->doctrineHelper->getSingleEntityIdentifierFieldName($marketingList->getEntity());
        if (empty($result[$idName])) {
            throw new \InvalidArgumentException('Result row must contain identifier field');
        }

        return $this->contact($marketingList, $result[$idName]);
    }
}
