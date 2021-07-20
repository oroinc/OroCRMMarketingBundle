<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

interface VisitEventAssociationExtensionAwareInterface
{
    /**
     * Sets the identifier tracking visit association
     */
    public function setVisitEventAssociationExtension(VisitEventAssociationExtension $extension);
}
