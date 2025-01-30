<?php

namespace Oro\Bundle\TrackingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroTrackingBundle_Entity_TrackingEvent;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\TrackingBundle\Entity\Repository\TrackingEventRepository;

/**
 * Represent a tracking event.
 *
 * @mixin OroTrackingBundle_Entity_TrackingEvent
 */
#[ORM\Entity(repositoryClass: TrackingEventRepository::class)]
#[ORM\Table(name: 'oro_tracking_event')]
#[ORM\Index(columns: ['name'], name: 'event_name_idx')]
#[ORM\Index(columns: ['logged_at'], name: 'event_loggedAt_idx')]
#[ORM\Index(columns: ['created_at'], name: 'event_createdAt_idx')]
#[ORM\Index(columns: ['parsed'], name: 'event_parsed_idx')]
#[ORM\Index(columns: ['code'], name: 'code_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-external-link'], 'grid' => ['default' => 'tracking-events-grid']])]
class TrackingEvent implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    public const INVALID_CODE = 'invalid';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrackingWebsite::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?TrackingWebsite $website = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    protected ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'value', type: Types::FLOAT, nullable: true)]
    protected $value;

    #[ORM\Column(name: 'user_identifier', type: Types::STRING, length: 255)]
    protected ?string $userIdentifier = null;

    #[ORM\Column(name: 'url', type: Types::TEXT)]
    protected ?string $url = null;

    #[ORM\Column(name: 'title', type: Types::TEXT, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(name: 'code', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $code = null;

    #[ORM\Column(name: 'parsed', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected ?bool $parsed = false;

    #[ORM\OneToOne(mappedBy: 'event', targetEntity: TrackingData::class)]
    protected ?TrackingData $eventData = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.created_at']])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'logged_at', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $loggedAt = null;

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->parsed = false;
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
     * Set name
     *
     * @param string $name
     * @return TrackingEvent
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
     * Set value
     *
     * @param float $value
     * @return TrackingEvent
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set userIdentifier
     *
     * @param string $userIdentifier
     * @return TrackingEvent
     */
    public function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * Get userIdentifier
     *
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return TrackingEvent
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return TrackingEvent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return TrackingEvent
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return TrackingEvent
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
     * Set loggedAt
     *
     * @param \DateTime $loggedAt
     * @return TrackingEvent
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
     * @return TrackingEvent
     */
    public function setWebsite(?TrackingWebsite $website = null)
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
     * @return boolean
     */
    public function isParsed()
    {
        return $this->parsed;
    }

    /**
     * @param boolean $parsed
     * @return $this
     */
    public function setParsed($parsed)
    {
        $this->parsed = $parsed;

        return $this;
    }

    /**
     * @return TrackingData
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * @param TrackingData $eventData
     * @return $this
     */
    public function setEventData($eventData)
    {
        $this->eventData = $eventData;

        return $this;
    }
}
