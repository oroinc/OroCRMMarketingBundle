<?php

namespace Oro\Bundle\TrackingBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;

/**
* Entity that represents Tracking Event Dictionary
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'oro_tracking_event_dictionary')]
#[ORM\HasLifecycleCallbacks]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-external-link']])]
class TrackingEventDictionary
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    protected ?string $name = null;

    #[ORM\ManyToOne(targetEntity: TrackingWebsite::class)]
    #[ORM\JoinColumn(name: 'website_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected ?TrackingWebsite $website = null;

    /**
     * @var Collection<int, TrackingVisitEvent>
     **/
    #[ORM\OneToMany(mappedBy: 'event', targetEntity: TrackingVisitEvent::class)]
    protected ?Collection $visitEvents = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return TrackingVisitEvent[]
     */
    public function getVisitEvents()
    {
        return $this->visitEvents;
    }

    /**
     * @param TrackingVisitEvent[] $visitEvents
     * @return $this
     */
    public function setVisitEvents($visitEvents)
    {
        $this->visitEvents = $visitEvents;

        return $this;
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
}
