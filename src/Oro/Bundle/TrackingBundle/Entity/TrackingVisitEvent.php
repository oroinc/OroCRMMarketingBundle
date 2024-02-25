<?php

namespace Oro\Bundle\TrackingBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroTrackingBundle_Entity_TrackingVisitEvent;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\TrackingBundle\Entity\Repository\TrackingVisitEventRepository;

/**
 * Tracking Visit Event Entity
 * @method TrackingVisitEvent supportAssociationTarget($targetClass)
 * @method TrackingVisitEvent getAssociationTargets()
 * @method TrackingVisitEvent hasAssociationTarget($target)
 * @method TrackingVisitEvent addAssociationTarget($target)
 * @method TrackingVisitEvent removeAssociationTarget($target)
 * @mixin OroTrackingBundle_Entity_TrackingVisitEvent
 */
#[ORM\Entity(repositoryClass: TrackingVisitEventRepository::class)]
#[ORM\Table(name: 'oro_tracking_visit_event')]
#[ORM\HasLifecycleCallbacks]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-external-link']])]
class TrackingVisitEvent implements ExtendEntityInterface
{
    use ExtendEntityTrait;

    public const INVALID_CODE = 'invalid';

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TrackingVisit::class)]
    #[ORM\JoinColumn(name: 'visit_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?TrackingVisit $visit = null;

    #[ORM\ManyToOne(targetEntity: TrackingEventDictionary::class, fetch: 'EXTRA_LAZY', inversedBy: 'visitEvents')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?TrackingEventDictionary $event = null;

    #[ORM\OneToOne(targetEntity: TrackingEvent::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(name: 'web_event_id', referencedColumnName: 'id')]
    protected ?TrackingEvent $webEvent = null;

    #[ORM\ManyToOne(targetEntity: TrackingWebsite::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?TrackingWebsite $website = null;

    #[ORM\Column(name: 'parsing_count', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    protected ?int $parsingCount = 0;

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
     * @return TrackingVisit
     */
    public function getVisit()
    {
        return $this->visit;
    }

    /**
     * @param TrackingVisit $visit
     * @return $this
     */
    public function setVisit($visit)
    {
        $this->visit = $visit;

        return $this;
    }

    /**
     * @return TrackingEvent
     */
    public function getWebEvent()
    {
        return $this->webEvent;
    }

    /**
     * @param TrackingEvent $webEvent
     * @return $this
     */
    public function setWebEvent($webEvent)
    {
        $this->webEvent = $webEvent;

        return $this;
    }

    /**
     * @return TrackingEventDictionary
     * @return $this
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param TrackingEventDictionary $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;

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
     * @return TrackingWebsite
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param TrackingWebsite $website
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->website = $website;

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
