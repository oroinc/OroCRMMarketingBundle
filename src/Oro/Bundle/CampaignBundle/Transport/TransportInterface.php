<?php

namespace Oro\Bundle\CampaignBundle\Transport;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;

/**
 * Represents a transport to send campaigns emails.
 */
interface TransportInterface
{
    /**
     * @param EmailCampaign $campaign
     * @param object $entity
     * @param string[] $from Associative array, key is sender email, value is sender name
     * @param string[] $to
     * @return mixed
     */
    public function send(EmailCampaign $campaign, object $entity, array $from, array $to);

    /**
     * Get transport name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get label used for transport selection.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns form type name needed to setup transport.
     *
     * @return string
     */
    public function getSettingsFormType();

    /**
     * Returns entity name needed to store transport settings.
     *
     * @return string
     */
    public function getSettingsEntityFQCN();
}
