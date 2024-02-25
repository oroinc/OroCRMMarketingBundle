<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Abstract class for transport settings
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_cmpgn_transport_stngs')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', length: 50)]
abstract class TransportSettings
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @var ParameterBag
     */
    protected $settings;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ParameterBag
     */
    abstract public function getSettingsBag();
}
