<?php

namespace Oro\Bundle\CampaignBundle\Transport;

/**
 * Defines the contract for email transports that support visibility control in forms.
 */
interface VisibilityTransportInterface
{
    /**
     * Determination of transport options in the form of creation.
     *
     * @return bool
     */
    public function isVisibleInForm();
}
