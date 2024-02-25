<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CampaignBundle\Entity\Repository\TrackingEventSummaryRepository;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

/**
* Entity that represents Tracking Event Summary
*
*/
#[ORM\Entity(repositoryClass: TrackingEventSummaryRepository::class)]
#[ORM\Table(name: 'orocrm_campaign_te_summary')]
#[ORM\Index(columns: ['name'], name: 'tes_event_name_idx')]
#[ORM\Index(columns: ['logged_at'], name: 'tes_event_loggedAt_idx')]
#[ORM\Index(columns: ['code'], name: 'tes_code_idx')]
#[ORM\Index(columns: ['visit_count'], name: 'tes_visits_idx')]
class TrackingEventSummary
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrackingWebsite::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?TrackingWebsite $website = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    protected ?string $name = null;

    #[ORM\Column(name: 'code', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $code = null;

    #[ORM\Column(name: 'visit_count', type: Types::INTEGER)]
    protected ?int $visitCount = null;

    #[ORM\Column(name: 'logged_at', type: Types::DATE_MUTABLE)]
    protected ?\DateTimeInterface $loggedAt = null;

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
     * Set name
     *
     * @param string $name
     * @return TrackingEventSummary
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set code
     *
     * @param string $code
     * @return TrackingEventSummary
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set loggedAt
     *
     * @param \DateTime $loggedAt
     * @return TrackingEventSummary
     */
    public function setLoggedAt($loggedAt)
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    /**
     * Get loggedAt
     *
     * @return \DateTime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * Set website
     *
     * @param TrackingWebsite|null $website
     * @return TrackingEventSummary
     */
    public function setWebsite(TrackingWebsite $website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return TrackingWebsite
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return int
     */
    public function getVisitCount()
    {
        return $this->visitCount;
    }

    /**
     * @param int $visitCount
     * @return TrackingEventSummary
     */
    public function setVisitCount($visitCount)
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
