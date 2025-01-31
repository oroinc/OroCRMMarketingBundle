<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroMarketingListBundle_Entity_MarketingList;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListSelectType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Marketing list
 *
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @mixin OroMarketingListBundle_Entity_MarketingList
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_marketing_list')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_marketing_list_index',
    defaultValues: [
        'entity' => ['icon' => 'fa-list-alt'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'marketing'],
        'form' => ['form_type' => MarketingListSelectType::class, 'grid_name' => 'oro-marketing-list-select-grid'],
        'grid' => ['default' => 'oro-marketing-list-grid'],
        'tag' => ['enabled' => true]
    ]
)]
class MarketingList implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    protected ?string $name = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: 'entity', type: Types::STRING, length: 255, unique: false, nullable: false)]
    protected ?string $entity = null;

    #[ORM\ManyToOne(targetEntity: MarketingListType::class)]
    #[ORM\JoinColumn(name: 'type', referencedColumnName: 'name', nullable: false)]
    protected ?MarketingListType $type = null;

    #[ORM\ManyToOne(targetEntity: Segment::class, cascade: ['all'])]
    #[ORM\JoinColumn(name: 'segment_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Segment $segment = null;

    /**
     * @var Collection<int, MarketingListItem>
     */
    #[ORM\OneToMany(
        mappedBy: 'marketingList',
        targetEntity: MarketingListItem::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    protected ?Collection $marketingListItems = null;

    /**
     * @var Collection<int, MarketingListUnsubscribedItem>
     */
    #[ORM\OneToMany(
        mappedBy: 'marketingList',
        targetEntity: MarketingListUnsubscribedItem::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    protected ?Collection $marketingListUnsubscribedItems = null;

    /**
     * @var Collection<int, MarketingListRemovedItem>
     */
    #[ORM\OneToMany(
        mappedBy: 'marketingList',
        targetEntity: MarketingListRemovedItem::class,
        cascade: ['all'],
        orphanRemoval: true
    )]
    protected ?Collection $marketingListRemovedItems = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    #[ORM\Column(name: 'last_run', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $lastRun = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.created_at']])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.updated_at']])]
    protected ?\DateTimeInterface $updatedAt = null;

    /**
     * Value to disable union, used to retrieve actual ML entities without MLI/MLRI/MLUI
     * @see \Oro\Bundle\MarketingListBundle\Datagrid\Extension\MarketingListExtension::isApplicable
     *
     * @var bool
     */
    #[ORM\Column(name: 'union_contacted_items', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    protected ?bool $union = true;

    public function __construct()
    {
        $this->marketingListItems = new ArrayCollection();
        $this->marketingListRemovedItems = new ArrayCollection();
        $this->marketingListUnsubscribedItems = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MarketingList
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MarketingList
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get marketing list type
     *
     * @return MarketingListType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set marketing list type
     *
     * @param MarketingListType $type
     * @return MarketingList
     */
    public function setType(MarketingListType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isManual()
    {
        if ($this->segment) {
            return false;
        }

        if ($this->type) {
            return $this->type->getName() === MarketingListType::TYPE_MANUAL;
        }

        return false;
    }

    /**
     * @return Segment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * @param Segment|null $segment
     * @return MarketingList
     */
    public function setSegment(?Segment $segment = null)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get the full name of an entity on which this marketing list is based
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the full name of an entity on which this marketing list is based
     *
     * @param string $entity
     * @return MarketingList
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get owner user.
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set a user owning this marketing list
     *
     * @param User $owning
     * @return MarketingList
     */
    public function setOwner(User $owning)
    {
        $this->owner = $owning;

        return $this;
    }

    /**
     * Get created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set created date/time
     *
     * @param \DateTime $created
     * @return MarketingList
     */
    public function setCreatedAt(\DateTime $created)
    {
        $this->createdAt = $created;

        return $this;
    }

    /**
     * Get last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set last update date/time
     *
     * @param \DateTime $updated
     * @return MarketingList
     */
    public function setUpdatedAt(\DateTime $updated)
    {
        $this->updatedAt = $updated;

        return $this;
    }

    /**
     * Set last run date/time
     *
     * @param \Datetime $lastRun
     * @return MarketingList
     */
    public function setLastRun($lastRun)
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    /**
     * Get last run date/time
     *
     * @return \Datetime
     */
    public function getLastRun()
    {
        return $this->lastRun;
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function doUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return MarketingListItem[]|Collection
     */
    public function getMarketingListItems()
    {
        return $this->marketingListItems;
    }

    /**
     * Set marketing list items.
     *
     * @param Collection|MarketingListItem[] $marketingListItems
     * @return MarketingList
     */
    public function resetMarketingListItems($marketingListItems)
    {
        $this->marketingListItems->clear();

        foreach ($marketingListItems as $marketingListItem) {
            $this->addMarketingListItem($marketingListItem);
        }

        return $this;
    }

    /**
     * Add marketing list item.
     *
     * @param MarketingListItem $marketingListItem
     * @return MarketingList
     */
    public function addMarketingListItem(MarketingListItem $marketingListItem)
    {
        if (!$this->marketingListItems->contains($marketingListItem)) {
            $this->marketingListItems->add($marketingListItem);
            $marketingListItem->setMarketingList($this);
        }

        return $this;
    }

    /**
     * Remove marketing list item.
     *
     * @param MarketingListItem $marketingListItem
     * @return MarketingList
     */
    public function removeMarketingListItem(MarketingListItem $marketingListItem)
    {
        if ($this->marketingListItems->contains($marketingListItem)) {
            $this->marketingListItems->removeElement($marketingListItem);
        }

        return $this;
    }

    /**
     * @return MarketingListItem[]|Collection
     */
    public function getMarketingListRemovedItems()
    {
        return $this->marketingListRemovedItems;
    }

    /**
     * Set marketing list removed items.
     *
     * @param Collection|MarketingListRemovedItem[] $marketingListRemovedItems
     * @return MarketingList
     */
    public function resetMarketingListRemovedItems($marketingListRemovedItems)
    {
        $this->marketingListRemovedItems->clear();

        foreach ($marketingListRemovedItems as $marketingListRemovedItem) {
            $this->addMarketingListRemovedItem($marketingListRemovedItem);
        }

        return $this;
    }

    /**
     * Add marketing list removed item.
     *
     * @param MarketingListRemovedItem $marketingListRemovedItem
     * @return MarketingList
     */
    public function addMarketingListRemovedItem(MarketingListRemovedItem $marketingListRemovedItem)
    {
        if (!$this->marketingListRemovedItems->contains($marketingListRemovedItem)) {
            $this->marketingListRemovedItems->add($marketingListRemovedItem);
            $marketingListRemovedItem->setMarketingList($this);
        }

        return $this;
    }

    /**
     * Remove marketing list item.
     *
     * @param MarketingListRemovedItem $marketingListRemovedItem
     * @return MarketingList
     */
    public function removeMarketingListRemovedItem(MarketingListRemovedItem $marketingListRemovedItem)
    {
        if ($this->marketingListRemovedItems->contains($marketingListRemovedItem)) {
            $this->marketingListRemovedItems->removeElement($marketingListRemovedItem);
        }

        return $this;
    }

    /**
     * @return MarketingListUnsubscribedItem[]|Collection
     */
    public function getMarketingListUnsubscribedItems()
    {
        return $this->marketingListUnsubscribedItems;
    }

    /**
     * Set marketing list unsubscribed items.
     *
     * This method could not be named setPhones because of bug CRM-253.
     *
     * @param Collection|MarketingListUnsubscribedItem[] $marketingListUnsubscribedItems
     * @return MarketingList
     */
    public function resetMarketingListUnsubscribedItems($marketingListUnsubscribedItems)
    {
        $this->marketingListUnsubscribedItems->clear();

        foreach ($marketingListUnsubscribedItems as $marketingListUnsubscribedItem) {
            $this->addMarketingListUnsubscribedItem($marketingListUnsubscribedItem);
        }

        return $this;
    }

    /**
     * Add marketing list unsubscribed item.
     *
     * @param MarketingListUnsubscribedItem $marketingListUnsubscribedItem
     * @return MarketingList
     */
    public function addMarketingListUnsubscribedItem(MarketingListUnsubscribedItem $marketingListUnsubscribedItem)
    {
        if (!$this->marketingListUnsubscribedItems->contains($marketingListUnsubscribedItem)) {
            $this->marketingListUnsubscribedItems->add($marketingListUnsubscribedItem);
            $marketingListUnsubscribedItem->setMarketingList($this);
        }

        return $this;
    }

    /**
     * Remove marketing list unsubscribed item.
     *
     * @param MarketingListUnsubscribedItem $marketingListUnsubscribedItem
     * @return MarketingList
     */
    public function removeMarketingListUnsubscribedItem(MarketingListUnsubscribedItem $marketingListUnsubscribedItem)
    {
        if ($this->marketingListUnsubscribedItems->contains($marketingListUnsubscribedItem)) {
            $this->marketingListUnsubscribedItems->removeElement($marketingListUnsubscribedItem);
        }

        return $this;
    }

    /**
     * Get this segment definition in YAML format
     *
     * @return string
     */
    public function getDefinition()
    {
        if ($this->segment) {
            return $this->segment->getDefinition();
        }

        return null;
    }

    /**
     * Set this segment definition in YAML format
     *
     * @param string $definition
     */
    public function setDefinition($definition)
    {
        if ($this->segment) {
            $this->segment->setDefinition($definition);
        }
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return MarketingList
     */
    public function setOrganization(?Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return bool
     */
    public function isUnion()
    {
        return $this->union;
    }
    /**
     * @param bool $union
     */
    public function setUnion($union)
    {
        $this->union = $union;
    }
}
