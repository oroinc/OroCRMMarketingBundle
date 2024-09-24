<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCampaignBundle_Entity_EmailCampaignStatistics;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignStatisticsRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents statistics of an email campaign.
 *
 * @mixin OroCampaignBundle_Entity_EmailCampaignStatistics
 */
#[ORM\Entity(repositoryClass: EmailCampaignStatisticsRepository::class)]
#[ORM\Table(name: 'orocrm_campaign_email_stats')]
#[ORM\UniqueConstraint(name: 'orocrm_ec_litem_unq', columns: ['email_campaign_id', 'marketing_list_item_id'])]
#[ORM\HasLifecycleCallbacks]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-bar-chart-o'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'marketing']
    ]
)]
class EmailCampaignStatistics implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MarketingListItem::class)]
    #[ORM\JoinColumn(name: 'marketing_list_item_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?MarketingListItem $marketingListItem = null;

    #[ORM\ManyToOne(targetEntity: EmailCampaign::class)]
    #[ORM\JoinColumn(name: 'email_campaign_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?EmailCampaign $emailCampaign = null;

    #[ORM\Column(name: 'open_count', type: Types::INTEGER, nullable: true)]
    protected ?int $openCount = null;

    #[ORM\Column(name: 'click_count', type: Types::INTEGER, nullable: true)]
    protected ?int $clickCount = null;

    #[ORM\Column(name: 'bounce_count', type: Types::INTEGER, nullable: true)]
    protected ?int $bounceCount = null;

    #[ORM\Column(name: 'abuse_count', type: Types::INTEGER, nullable: true)]
    protected ?int $abuseCount = null;

    #[ORM\Column(name: 'unsubscribe_count', type: Types::INTEGER, nullable: true)]
    protected ?int $unsubscribeCount = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MarketingListItem
     */
    public function getMarketingListItem()
    {
        return $this->marketingListItem;
    }

    /**
     * @param MarketingListItem $marketingListItem
     * @return EmailCampaignStatistics
     */
    public function setMarketingListItem(MarketingListItem $marketingListItem)
    {
        $this->marketingListItem = $marketingListItem;

        return $this;
    }

    /**
     * @return EmailCampaign
     */
    public function getEmailCampaign()
    {
        return $this->emailCampaign;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return EmailCampaignStatistics
     */
    public function setEmailCampaign(EmailCampaign $emailCampaign)
    {
        $this->emailCampaign = $emailCampaign;

        return $this;
    }

    /**
     * @return int
     */
    public function getOpenCount()
    {
        return $this->openCount;
    }

    /**
     * @param int $openCount
     * @return EmailCampaignStatistics
     */
    public function setOpenCount($openCount)
    {
        $this->openCount = $openCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementOpenCount()
    {
        $this->openCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getClickCount()
    {
        return $this->clickCount;
    }

    /**
     * @param int $clickCount
     * @return EmailCampaignStatistics
     */
    public function setClickCount($clickCount)
    {
        $this->clickCount = $clickCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementClickCount()
    {
        $this->clickCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getBounceCount()
    {
        return $this->bounceCount;
    }

    /**
     * @param int $bounceCount
     * @return EmailCampaignStatistics
     */
    public function setBounceCount($bounceCount)
    {
        $this->bounceCount = $bounceCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementBounceCount()
    {
        $this->bounceCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getAbuseCount()
    {
        return $this->abuseCount;
    }

    /**
     * @param int $abuseCount
     * @return EmailCampaignStatistics
     */
    public function setAbuseCount($abuseCount)
    {
        $this->abuseCount = $abuseCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementAbuseCount()
    {
        $this->abuseCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getUnsubscribeCount()
    {
        return $this->unsubscribeCount;
    }

    /**
     * @param int $unsubscribeCount
     * @return EmailCampaignStatistics
     */
    public function setUnsubscribeCount($unsubscribeCount)
    {
        $this->unsubscribeCount = $unsubscribeCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementUnsubscribeCount()
    {
        $this->unsubscribeCount++;

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
     * @param \DateTime $createdAt
     * @return EmailCampaignStatistics
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Pre persist event handler
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set owner
     *
     * @param User|null $owner
     *
     * @return EmailCampaignStatistics
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return EmailCampaignStatistics
     */
    public function setOrganization(Organization $organization = null)
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
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getId();
    }
}
