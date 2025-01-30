<?php

namespace Oro\Bundle\MarketingActivityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroMarketingActivityBundle_Entity_MarketingActivity;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\MarketingActivityBundle\Entity\Repository\MarketingActivityRepository;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * Store marketing activity in database.
 *
 * @method EnumOptionInterface getType()
 * @mixin OroMarketingActivityBundle_Entity_MarketingActivity
 */
#[ORM\Entity(repositoryClass: MarketingActivityRepository::class)]
#[ORM\Table(name: 'orocrm_marketing_activity')]
#[ORM\Index(columns: ['entity_id', 'entity_class'], name: 'IDX_MARKETING_ACTIVITY_ENTITY')]
#[ORM\Index(
    columns: ['related_campaign_id', 'related_campaign_class'],
    name: 'IDX_MARKETING_ACTIVITY_RELATED_CAMPAIGN'
)]
#[ORM\Index(columns: ['action_date'], name: 'IDX_MARKETING_ACTIVITY_ACTION_DATE')]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-user'],
        'ownership' => [
            'owner_type' => 'ORGANIZATION',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'marketing']
    ]
)]
class MarketingActivity implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    const TYPE_ENUM_CODE = 'ma_type';

    /** constant for enum ma_type */
    const TYPE_SEND        = 'send';
    const TYPE_OPEN        = 'open';
    const TYPE_CLICK       = 'click';
    const TYPE_SOFT_BOUNCE = 'soft_bounce';
    const TYPE_HARD_BOUNCE = 'hard_bounce';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Organization $owner = null;

    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    #[ORM\JoinColumn(name: 'campaign_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Campaign $campaign = null;

    #[ORM\Column(name: 'entity_id', type: Types::INTEGER, nullable: false)]
    protected ?int $entityId = null;

    #[ORM\Column(name: 'entity_class', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $entityClass = null;

    #[ORM\Column(name: 'related_campaign_id', type: Types::INTEGER, nullable: true)]
    protected ?int $relatedCampaignId = null;

    #[ORM\Column(name: 'related_campaign_class', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $relatedCampaignClass = null;

    #[ORM\Column(name: 'details', type: Types::TEXT, nullable: true)]
    protected ?string $details = null;

    #[ORM\Column(name: 'action_date', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $actionDate = null;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get owner organization
     *
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set owner organization
     *
     * @param Organization|null $owner
     * @return MarketingActivity
     */
    public function setOwner(?Organization $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set campaign
     *
     * @param Campaign|null $campaign
     * @return MarketingActivity
     */
    public function setCampaign(?Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get entity id
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set entity id
     *
     * @param int $entityId
     * @return MarketingActivity
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entity class
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set entity class
     *
     * @param string $entityClass
     * @return MarketingActivity
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get related campaign id
     *
     * @return int
     */
    public function getRelatedCampaignId()
    {
        return $this->relatedCampaignId;
    }

    /**
     * Set related campaign id
     *
     * @param int $relatedCampaignId
     * @return MarketingActivity
     */
    public function setRelatedCampaignId($relatedCampaignId)
    {
        $this->relatedCampaignId = $relatedCampaignId;

        return $this;
    }

    /**
     * Get related campaign class
     *
     * @return string
     */
    public function getRelatedCampaignClass()
    {
        return $this->relatedCampaignClass;
    }

    /**
     * Set related campaign class
     *
     * @param string $relatedCampaignClass
     * @return MarketingActivity
     */
    public function setRelatedCampaignClass($relatedCampaignClass)
    {
        $this->relatedCampaignClass = $relatedCampaignClass;

        return $this;
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set details
     *
     * @param string $details
     * @return MarketingActivity
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get action date/time
     *
     * @return \DateTime
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * Set action date/time
     *
     * @param \DateTime $actionDate
     * @return MarketingActivity
     */
    public function setActionDate(\DateTime $actionDate)
    {
        $this->actionDate = $actionDate;

        return $this;
    }
}
