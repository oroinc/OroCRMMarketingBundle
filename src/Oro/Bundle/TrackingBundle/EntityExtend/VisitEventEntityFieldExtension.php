<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\EntityExtend;

use Oro\Bundle\EntityExtendBundle\EntityExtend\AbstractAssociationEntityFieldExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;

/**
 * Extended Entity Field Processor Extension for oro_tracking_visit_event associations
 */
class VisitEventEntityFieldExtension extends AbstractAssociationEntityFieldExtension
{
    protected function isApplicable(EntityFieldProcessTransport $transport): bool
    {
        return $transport->getClass() === TrackingVisitEvent::class;
    }

    protected function getRelationKind(): ?string
    {
        return VisitEventAssociationExtension::ASSOCIATION_KIND;
    }

    protected function getRelationType(): string
    {
        return RelationType::MULTIPLE_MANY_TO_ONE;
    }
}
