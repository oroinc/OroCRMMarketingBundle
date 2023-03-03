<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\EntityExtend;

use Oro\Bundle\EntityExtendBundle\EntityExtend\AbstractAssociationEntityFieldExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;

/**
 * Extended Entity Field Processor Extension for oro_tracking_visit associations
 */
class IdentifierVisitEntityFieldExtension extends AbstractAssociationEntityFieldExtension
{
    protected function isApplicable(EntityFieldProcessTransport $transport): bool
    {
        return $transport->getClass() === TrackingVisit::class;
    }

    protected function getRelationKind(): ?string
    {
        return IdentifierEventExtension::ASSOCIATION_KIND;
    }

    protected function getRelationType(): string
    {
        return RelationType::MANY_TO_ONE;
    }
}
