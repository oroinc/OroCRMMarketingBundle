<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;

/**
* Entity that represents Marketing List Type
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_marketing_list_type')]
#[Config]
class MarketingListType
{
    public const TYPE_DYNAMIC = 'dynamic';
    public const TYPE_STATIC  = 'static';
    public const TYPE_MANUAL  = 'manual';

    #[ORM\Column(name: 'name', type: Types::STRING, length: 32)]
    #[ORM\Id]
    protected ?string $name = null;

    #[ORM\Column(name: 'label', type: Types::STRING, length: 255, unique: true)]
    protected ?string $label = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get type name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param string $label
     *
     * @return MarketingListType
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->label;
    }
}
