<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCampaignBundle_Entity_EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents an email campaign.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @mixin OroCampaignBundle_Entity_EmailCampaign
 */
#[ORM\Entity(repositoryClass: EmailCampaignRepository::class)]
#[ORM\Table(name: 'orocrm_campaign_email')]
#[ORM\Index(columns: ['owner_id'], name: 'cmpgn_email_owner_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_email_campaign_index',
    defaultValues: [
        'entity' => ['icon' => 'fa-envelope'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'marketing'],
        'grid' => ['default' => 'oro-email-campaign-grid'],
        'tag' => ['enabled' => true]
    ]
)]
class EmailCampaign implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    const SCHEDULE_MANUAL = 'manual';
    const SCHEDULE_DEFERRED = 'deferred';

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    protected ?string $name = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(name: 'is_sent', type: Types::BOOLEAN)]
    protected ?bool $sent = false;

    #[ORM\Column(name: 'sent_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $sentAt = null;

    #[ORM\Column(name: 'schedule', type: Types::STRING, length: 255)]
    protected ?string $schedule = null;

    #[ORM\Column(name: 'scheduled_for', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $scheduledFor = null;

    #[ORM\Column(name: 'sender_email', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $senderEmail = null;

    #[ORM\Column(name: 'sender_name', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $senderName = null;

    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    #[ORM\JoinColumn(name: 'campaign_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?Campaign $campaign = null;

    #[ORM\ManyToOne(targetEntity: MarketingList::class)]
    #[ORM\JoinColumn(name: 'marketing_list_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?MarketingList $marketingList = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?User $owner = null;

    #[ORM\Column(name: 'transport', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $transport = null;

    #[ORM\OneToOne(targetEntity: TransportSettings::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'transport_settings_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?TransportSettings $transportSettings = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.created_at']])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.updated_at']])]
    protected ?\DateTimeInterface $updatedAt = null;

    /**
     * Pre persist event handler
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return null|string
     */
    public function getEntityName()
    {
        if ($this->marketingList) {
            return $this->marketingList->getEntity();
        }

        return null;
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return EmailCampaign
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return EmailCampaign
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set campaign
     *
     * @param Campaign|null $campaign
     *
     * @return EmailCampaign
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

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
     * Set marketingList
     *
     * @param MarketingList $marketingList
     *
     * @return EmailCampaign
     */
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * Get marketingList
     *
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * Set owner
     *
     * @param User|null $owner
     *
     * @return EmailCampaign
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EmailCampaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return EmailCampaign
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set sent
     *
     * @param boolean $sent
     * @return EmailCampaign
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
        $this->sentAt = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    /**
     * Get isSent
     *
     * @return boolean
     */
    public function isSent()
    {
        return $this->sent;
    }

    /**
     * Set schedule
     *
     * @param string $schedule
     * @return EmailCampaign
     */
    public function setSchedule($schedule)
    {
        $types = [self::SCHEDULE_MANUAL, self::SCHEDULE_DEFERRED];

        if (!in_array($schedule, $types)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Schedule type %s is not know. Known types are %s',
                    $schedule,
                    implode(', ', $types)
                )
            );
        }
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule
     *
     * @return string
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    public function getScheduledFor(): ?\DateTime
    {
        return $this->scheduledFor;
    }

    /**
     * @param \DateTime|null $scheduledFor
     * @return EmailCampaign
     */
    public function setScheduledFor(\DateTime $scheduledFor = null)
    {
        $this->scheduledFor = $scheduledFor;

        return $this;
    }

    /**
     * Set Sender Email address.
     *
     * @param string $senderEmail
     * @return EmailCampaign
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    /**
     * Get Sender Email address.
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Set Sender Name.
     *
     * @param string $senderName
     * @return EmailCampaign
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;

        return $this;
    }

    /**
     * Get Sender Name.
     *
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Set sentAt
     *
     * @param \DateTime $sentAt
     * @return EmailCampaign
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param string $transport
     * @return EmailCampaign
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @return TransportSettings
     */
    public function getTransportSettings()
    {
        return $this->transportSettings;
    }

    /**
     * @param TransportSettings $transportSettings
     * @return EmailCampaign
     */
    public function setTransportSettings($transportSettings)
    {
        $this->transportSettings = $transportSettings;

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return EmailCampaign
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
        return (string)$this->getName();
    }
}
