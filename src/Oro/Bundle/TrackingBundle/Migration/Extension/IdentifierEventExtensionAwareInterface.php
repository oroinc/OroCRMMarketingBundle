<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

interface IdentifierEventExtensionAwareInterface
{
    /**
     * Sets the identifier tracking visit association
     */
    public function setIdentifierEventExtension(IdentifierEventExtension $extension);
}
