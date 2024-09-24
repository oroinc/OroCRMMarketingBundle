<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Marketing list unsubscribed item.
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_ml_item_uns')]
#[ORM\UniqueConstraint(name: 'orocrm_ml_list_ent_uns_unq', columns: ['entity_id', 'marketing_list_id'])]
#[ORM\HasLifecycleCallbacks]
class MarketingListUnsubscribedItem implements MarketingListStateItemInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'entity_id', type: Types::INTEGER, nullable: false)]
    protected ?int $entityId = null;

    #[ORM\ManyToOne(targetEntity: MarketingList::class, inversedBy: 'marketingListUnsubscribedItems')]
    #[ORM\JoinColumn(name: 'marketing_list_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?MarketingList $marketingList = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $createdAt = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return MarketingListUnsubscribedItem
     */
    #[\Override]
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    #[\Override]
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     *
     * @return MarketingListUnsubscribedItem
     */
    #[\Override]
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    #[\Override]
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * @param \Datetime $createdAt
     * @return MarketingListUnsubscribedItem
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
