<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
* Entity that represents Internal Transport Settings
*
*/
#[ORM\Entity]
class InternalTransportSettings extends TransportSettings
{
    #[ORM\ManyToOne(targetEntity: EmailTemplate::class)]
    #[ORM\JoinColumn(name: 'email_template_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    protected ?EmailTemplate $template = null;

    /**
     * Set template
     *
     * @param EmailTemplate|null $emailTemplate
     *
     * @return InternalTransportSettings
     */
    public function setTemplate(EmailTemplate $emailTemplate = null)
    {
        $this->template = $emailTemplate;

        return $this;
    }

    /**
     * Get template
     *
     * @return EmailTemplate|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                array(
                    'template' => $this->getTemplate()
                )
            );
        }

        return $this->settings;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
