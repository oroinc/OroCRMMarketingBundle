<?php

namespace Oro\Bundle\MarketingActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * Store marketing activity in database.
 *
 * @ORM\Entity(repositoryClass="Oro\Bundle\MarketingActivityBundle\Entity\Repository\MarketingActivityRepository")
 * @ORM\Table(
 *     name="orocrm_marketing_activity",
 *     indexes={
 *          @ORM\Index(name="IDX_MARKETING_ACTIVITY_ENTITY", columns={"entity_id", "entity_class"}),
 *          @ORM\Index(
 *              name="IDX_MARKETING_ACTIVITY_RELATED_CAMPAIGN",
 *              columns={"related_campaign_id", "related_campaign_class"
 *          }),
 *          @ORM\Index(name="IDX_MARKETING_ACTIVITY_ACTION_DATE", columns={"action_date"})
 *     }
 * )
 * @Config(
 *  defaultValues={
 *      "entity"={
 *          "icon"="fa-user"
 *      },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"="",
 *          "category"="marketing"
 *      }
 *  }
 * )
 * @method AbstractEnumValue getType()
 */
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

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\CampaignBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $campaign;

    /**
     * @var int
     *
     * @ORM\Column(name="entity_id", type="integer", nullable=false)
     */
    protected $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_class", type="string", length=255, nullable=false)
     */
    protected $entityClass;

    /**
     * @var int
     *
     * @ORM\Column(name="related_campaign_id", type="integer", nullable=true)
     */
    protected $relatedCampaignId;

    /**
     * @var string
     *
     * @ORM\Column(name="related_campaign_class", type="string", length=255, nullable=true)
     */
    protected $relatedCampaignClass;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=true)
     */
    protected $details;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="action_date", type="datetime")
     */
    protected $actionDate;

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
     * @param Organization $owner
     * @return MarketingActivity
     */
    public function setOwner(Organization $owner = null)
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
     * @param Campaign $campaign
     * @return MarketingActivity
     */
    public function setCampaign(Campaign $campaign = null)
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
