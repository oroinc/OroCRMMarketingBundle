<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;

/**
 * Marketing list item.
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_marketing_list_item')]
#[ORM\UniqueConstraint(name: 'orocrm_ml_list_ent_unq', columns: ['entity_id', 'marketing_list_id'])]
#[ORM\HasLifecycleCallbacks]
#[Config]
class MarketingListItem
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'entity_id', type: Types::INTEGER, nullable: false)]
    protected ?int $entityId = null;

    #[ORM\Column(name: 'contacted_times', type: Types::INTEGER, nullable: true)]
    protected ?int $contactedTimes = null;

    #[ORM\ManyToOne(targetEntity: MarketingList::class, inversedBy: 'marketingListItems')]
    #[ORM\JoinColumn(name: 'marketing_list_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?MarketingList $marketingList = null;

    #[ORM\Column(name: 'last_contacted_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $lastContactedAt = null;

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
     * @param int $entityId
     * @return MarketingListItem
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param MarketingList $marketingList
     * @return MarketingListItem
     */
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * @param \DateTime $createdAt
     * @return MarketingListItem
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
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

    /**
     * @return \DateTime
     */
    public function getLastContactedAt()
    {
        return $this->lastContactedAt;
    }

    /**
     * @param \DateTime $lastContactedAt
     * @return MarketingListItem
     */
    public function setLastContactedAt($lastContactedAt)
    {
        $this->lastContactedAt = $lastContactedAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getContactedTimes()
    {
        return $this->contactedTimes;
    }

    /**
     * @param int $contactedTimes
     * @return MarketingListItem
     */
    public function setContactedTimes($contactedTimes)
    {
        $this->contactedTimes = $contactedTimes;

        return $this;
    }

    /**
     * Update contact activity.
     */
    public function contact()
    {
        $this->setLastContactedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->setContactedTimes((int)$this->getContactedTimes() + 1);
    }
}
