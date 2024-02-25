<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCampaignBundle_Entity_Campaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Form\Type\CampaignSelectType;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Represents a marketing campaign help track marketing budgets and outcomes of marketing actions.
 *
 * @mixin OroCampaignBundle_Entity_Campaign
 */
#[ORM\Entity(repositoryClass: CampaignRepository::class)]
#[ORM\Table(name: 'orocrm_campaign')]
#[ORM\Index(columns: ['owner_id'], name: 'cmpgn_owner_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_campaign_index',
    routeView: 'oro_campaign_view',
    defaultValues: [
        'entity' => ['icon' => 'fa-volume-up'],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'marketing'],
        'form' => ['form_type' => CampaignSelectType::class, 'grid_name' => 'oro-campaign-grid'],
        'grid' => ['default' => 'oro-campaign-grid'],
        'tag' => ['enabled' => true],
        'merge' => ['enable' => true]
    ]
)]
class Campaign implements ExtendEntityInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;

    const PERIOD_HOURLY  = 'hour';
    const PERIOD_DAILY   = 'day';
    const PERIOD_MONTHLY = 'month';
    const PERIOD_YEARLY  = 'year';

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    #[ConfigField(defaultValues: ['merge' => ['display' => true]])]
    protected ?string $name = null;

    #[ORM\Column(name: 'code', type: Types::STRING, length: 255, unique: true)]
    protected ?string $code = null;

    /**
     * This field needed as label in related entities drown select
     */
    #[ORM\Column(name: 'combined_name', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $combinedName = null;

    #[ORM\Column(name: 'start_date', type: Types::DATE_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['merge' => ['display' => true]])]
    protected ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'end_date', type: Types::DATE_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['merge' => ['display' => true]])]
    protected ?\DateTimeInterface $endDate = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    #[ConfigField(defaultValues: ['merge' => ['display' => true]])]
    protected ?string $description = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'budget', type: 'money', nullable: true)]
    #[ConfigField(defaultValues: ['merge' => ['display' => true]])]
    protected $budget;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['merge' => ['display' => true]])]
    protected ?User $owner = null;

    #[ORM\Column(name: 'report_period', type: Types::STRING, length: 25)]
    protected ?string $reportPeriod = null;

    #[ORM\Column(name: 'report_refresh_date', type: Types::DATE_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $reportRefreshDate = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reportPeriod = self::PERIOD_DAILY;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param float $budget
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;
    }

    /**
     * @return float
     */
    public function getBudget()
    {
        return $this->budget;
    }

    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Pre persist event handler
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->setCombinedName($this->generateCombinedName($this->name, $this->code));
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->setCombinedName($this->generateCombinedName($this->name, $this->code));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set combined name in format "campaign name (campaign_code)"
     *
     * @param string $combinedName
     */
    public function setCombinedName($combinedName)
    {
        $this->combinedName = $combinedName;
    }

    /**
     * @return string
     */
    public function getCombinedName()
    {
        return $this->combinedName;
    }

    /**
     * Generate combined name in format "campaign name (campaign_code)"
     *
     * @param string $name
     * @param string $code
     * @return string
     */
    public function generateCombinedName($name, $code)
    {
        return sprintf('%s (%s)', $name, $code);
    }

    /**
     *  Get report period.
     *
     * @return string
     */
    public function getReportPeriod()
    {
        return $this->reportPeriod;
    }

    /**
     * Set report period.
     *
     * @param string $reportPeriod
     * @return Campaign
     */
    public function setReportPeriod($reportPeriod)
    {
        $this->reportPeriod = $reportPeriod;

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return Campaign
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
    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * @return \DateTime
     */
    public function getReportRefreshDate()
    {
        return $this->reportRefreshDate;
    }

    /**
     * @param \DateTime $reportRefreshDate
     * @return Campaign
     */
    public function setReportRefreshDate($reportRefreshDate)
    {
        $this->reportRefreshDate = $reportRefreshDate;

        return $this;
    }
}
