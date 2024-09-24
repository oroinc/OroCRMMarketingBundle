<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
* Entity that represents Campaign Code History
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_campaign_code_history')]
class CampaignCodeHistory
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Campaign::class)]
    #[ORM\JoinColumn(name: 'campaign_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected ?Campaign $campaign = null;

    #[ORM\Column(name: 'code', type: Types::STRING, length: 255, unique: true)]
    protected ?string $code = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * Set campaign
     *
     * @param Campaign $campaign
     * @return $this
     */
    public function setCampaign(Campaign $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set campaign code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getCode();
    }
}
