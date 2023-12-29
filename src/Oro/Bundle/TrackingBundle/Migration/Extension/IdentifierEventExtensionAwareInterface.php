<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

/**
 * This interface should be implemented by migrations that depend on {@see IdentifierEventExtension}.
 */
interface IdentifierEventExtensionAwareInterface
{
    public function setIdentifierEventExtension(IdentifierEventExtension $extension);
}
