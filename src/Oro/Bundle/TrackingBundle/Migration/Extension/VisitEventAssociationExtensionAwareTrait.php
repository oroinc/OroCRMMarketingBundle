<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

/**
 * This trait can be used by migrations that implement {@see VisitEventAssociationExtensionAwareInterface}.
 */
trait VisitEventAssociationExtensionAwareTrait
{
    private VisitEventAssociationExtension $visitEventAssociationExtension;

    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension): void
    {
        $this->visitEventAssociationExtension = $extension;
    }
}
