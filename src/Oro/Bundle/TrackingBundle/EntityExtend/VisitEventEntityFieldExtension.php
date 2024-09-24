<?php

declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\EntityExtend;

use Oro\Bundle\EntityExtendBundle\EntityExtend\AbstractAssociationEntityFieldExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\EntityExtendBundle\Tools\AssociationNameGenerator;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;

/**
 * Extended Entity Field Processor Extension for oro_tracking_visit_event associations
 */
class VisitEventEntityFieldExtension extends AbstractAssociationEntityFieldExtension
{
    #[\Override]
    public function isApplicable(EntityFieldProcessTransport $transport): bool
    {
        return $transport->getClass() === TrackingVisitEvent::class
            && AssociationNameGenerator::extractAssociationKind($transport->getName()) === $this->getRelationKind();
    }

    #[\Override]
    public function getRelationKind(): ?string
    {
        return VisitEventAssociationExtension::ASSOCIATION_KIND;
    }

    #[\Override]
    public function getRelationType(): string
    {
        return RelationType::MULTIPLE_MANY_TO_ONE;
    }
}
