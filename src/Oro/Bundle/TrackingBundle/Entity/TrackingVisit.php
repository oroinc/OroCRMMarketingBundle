<?php

namespace Oro\Bundle\TrackingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroTrackingBundle_Entity_TrackingVisit;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\TrackingBundle\Entity\Repository\TrackingVisitRepository;

/**
 * Represent a website visit.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @method TrackingVisit supportIdentifierTarget($targetClass)
 * @method TrackingVisit setIdentifierTarget($target)
 * @method TrackingVisit getIdentifierTarget()
 * @mixin OroTrackingBundle_Entity_TrackingVisit
 */
#[ORM\Entity(repositoryClass: TrackingVisitRepository::class)]
#[ORM\Table(name: 'oro_tracking_visit')]
#[ORM\Index(columns: ['visitor_uid'], name: 'visit_visitorUid_idx')]
#[ORM\Index(columns: ['user_identifier'], name: 'visit_userIdentifier_idx')]
#[ORM\Index(columns: ['website_id', 'first_action_time'], name: 'website_first_action_time_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-external-link']])]
class TrackingVisit implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    public const INVALID_CODE = 'invalid';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrackingWebsite::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?TrackingWebsite $trackingWebsite = null;

    #[ORM\Column(name: 'visitor_uid', type: Types::STRING, length: 255)]
    protected ?string $visitorUid = null;

    #[ORM\Column(name: 'user_identifier', type: Types::STRING, length: 255)]
    protected ?string $userIdentifier = null;

    #[ORM\Column(name: 'first_action_time', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $firstActionTime = null;

    #[ORM\Column(name: 'last_action_time', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $lastActionTime = null;

    #[ORM\Column(name: 'parsed_uid', type: Types::INTEGER, length: 255, nullable: false, options: ['default' => 0])]
    protected ?int $parsedUID = 0;

    #[ORM\Column(name: 'identifier_detected', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected ?bool $identifierDetected = false;

    #[ORM\Column(name: 'parsing_count', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    protected ?int $parsingCount = 0;

    #[ORM\Column(name: 'ip', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $ip = null;

    #[ORM\Column(name: 'client', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $client = null;

    #[ORM\Column(name: 'client_version', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $clientVersion = null;

    #[ORM\Column(name: 'client_type', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $clientType = null;

    #[ORM\Column(name: 'os', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $os = null;

    #[ORM\Column(name: 'os_version', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $osVersion = null;

    #[ORM\Column(name: 'desktop', type: Types::BOOLEAN, nullable: true)]
    protected ?bool $desktop = null;

    #[ORM\Column(name: 'mobile', type: Types::BOOLEAN, nullable: true)]
    protected ?bool $mobile = null;

    #[ORM\Column(name: 'bot', type: Types::BOOLEAN, nullable: true)]
    protected ?bool $bot = null;

    #[ORM\Column(name: 'code', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $code = null;

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
     * @param TrackingWebsite $trackingWebsite
     * @return $this
     */
    public function setTrackingWebsite($trackingWebsite)
    {
        $this->trackingWebsite = $trackingWebsite;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisitorUid()
    {
        return $this->visitorUid;
    }

    /**
     * @param string $visitorUid
     * @return $this
     */
    public function setVisitorUid($visitorUid)
    {
        $this->visitorUid = $visitorUid;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

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
     * @return \DateTime
     */
    public function getLastActionTime()
    {
        return $this->lastActionTime;
    }

    /**
     * @param \DateTime $lastActionTime
     * @return $this
     */
    public function setLastActionTime(\DateTime $lastActionTime)
    {
        $this->lastActionTime = $lastActionTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getParsingCount()
    {
        return $this->parsingCount;
    }

    /**
     * @param int $parsingCount
     * @return $this
     */
    public function setParsingCount($parsingCount)
    {
        $this->parsingCount = $parsingCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getParsedUID()
    {
        return $this->parsedUID;
    }

    /**
     * @param string $parsedUID
     * @return $this
     */
    public function setParsedUID($parsedUID)
    {
        $this->parsedUID = $parsedUID;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIdentifierDetected()
    {
        return $this->identifierDetected;
    }

    /**
     * @param boolean $identifierDetected
     */
    public function setIdentifierDetected($identifierDetected)
    {
        $this->identifierDetected = $identifierDetected;
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $client
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @param string $os
     * @return $this
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDesktop()
    {
        return $this->desktop;
    }

    /**
     * @param boolean $desktop
     *
     * @return $this
     */
    public function setDesktop($desktop)
    {
        $this->desktop = $desktop;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMobile()
    {
        return $this->mobile;
    }

    /**
     * @param boolean $mobile
     *
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientVersion()
    {
        return $this->clientVersion;
    }

    /**
     * @param string $clientVersion
     *
     * @return $this
     */
    public function setClientVersion($clientVersion)
    {
        $this->clientVersion = $clientVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientType()
    {
        return $this->clientType;
    }

    /**
     * @param string $clientType
     *
     * @return $this
     */
    public function setClientType($clientType)
    {
        $this->clientType = $clientType;

        return $this;
    }

    /**
     * @return string
     */
    public function getOsVersion()
    {
        return $this->osVersion;
    }

    /**
     * @param string $osVersion
     *
     * @return $this
     */
    public function setOsVersion($osVersion)
    {
        $this->osVersion = $osVersion;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isBot()
    {
        return $this->bot;
    }

    /**
     * @param boolean $bot
     *
     * @return $this
     */
    public function setBot($bot)
    {
        $this->bot = $bot;

        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
