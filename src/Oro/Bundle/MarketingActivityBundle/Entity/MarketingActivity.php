<?php

namespace Oro\Bundle\MarketingActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\CampaignBundle\Entity\Campaign;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_marketing_activity")
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
 */
class MarketingActivity
{
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Organization $owner
     *
     * @return MarketingActivity
     */
    public function setOwner(Organization $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param Campaign $campaign
     *
     * @return MarketingActivity
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

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
     * @param int $entityId
     *
     * @return MarketingActivity
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityClass
     *
     * @return MarketingActivity
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getRelatedCampaignId()
    {
        return $this->relatedCampaignId;
    }

    /**
     * @param int $relatedCampaignId
     *
     * @return MarketingActivity
     */
    public function setRelatedCampaignId($relatedCampaignId)
    {
        $this->relatedCampaignId = $relatedCampaignId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelatedCampaignClass()
    {
        return $this->relatedCampaignClass;
    }

    /**
     * @param string $relatedCampaignClass
     *
     * @return MarketingActivity
     */
    public function setRelatedCampaignClass($relatedCampaignClass)
    {
        $this->relatedCampaignClass = $relatedCampaignClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     *
     * @return MarketingActivity
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * @param \DateTime $actionDate
     *
     * @return MarketingActivity
     */
    public function setActionDate($actionDate)
    {
        $this->actionDate = $actionDate;

        return $this;
    }
}
