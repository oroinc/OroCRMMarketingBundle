<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

/**
 * This interface should be implemented by migrations that depend on {@see VisitEventAssociationExtension}.
 */
interface VisitEventAssociationExtensionAwareInterface
{
    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension);
}
