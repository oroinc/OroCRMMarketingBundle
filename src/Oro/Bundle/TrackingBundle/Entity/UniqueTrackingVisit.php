<?php

namespace Oro\Bundle\TrackingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\TrackingBundle\Entity\Repository\UniqueTrackingVisitRepository;

/**
* Entity that represents Unique Tracking Visit
*
*/
#[ORM\Entity(repositoryClass: UniqueTrackingVisitRepository::class)]
#[ORM\Table(name: 'oro_tracking_unique_visit')]
#[ORM\Index(columns: ['website_id', 'action_date'], name: 'uvisit_action_date_idx')]
#[ORM\Index(columns: ['user_identifier', 'action_date'], name: 'uvisit_user_by_date_idx')]
class UniqueTrackingVisit
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrackingWebsite::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?TrackingWebsite $trackingWebsite = null;

    #[ORM\Column(name: 'visit_count', type: Types::INTEGER, nullable: false)]
    protected ?int $visitCount = null;

    #[ORM\Column(name: 'user_identifier', type: Types::STRING, length: 32)]
    protected ?string $userIdentifier = null;

    #[ORM\Column(name: 'action_date', type: Types::DATE_MUTABLE)]
    protected ?\DateTimeInterface $firstActionTime = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TrackingWebsite
     */
    public function getTrackingWebsite()
    {
        return $this->trackingWebsite;
    }

    /**
     * @param TrackingWebsite|null $trackingWebsite
     * @return $this
     */
    public function setTrackingWebsite(TrackingWebsite $trackingWebsite = null)
    {
        $this->trackingWebsite = $trackingWebsite;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * @param string $userIdentifier
     * @return $this
     */
    public function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getFirstActionTime()
    {
        return $this->firstActionTime;
    }

    /**
     * @param \DateTime $firstActionTime
     * @return $this
     */
    public function setFirstActionTime(\DateTime $firstActionTime)
    {
        $this->firstActionTime = $firstActionTime;

        return $this;
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
     * @return $this
     */
    public function setVisitCount($visitCount)
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    public function increaseVisitCount()
    {
        $this->visitCount++;
    }
}
