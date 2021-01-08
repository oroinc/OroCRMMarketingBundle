<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tools;

use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\EntityExtendBundle\Tools\GeneratorExtensions\AbstractAssociationEntityGeneratorExtension;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;

/**
 * Generates PHP code for multiple-many-to-one VisitEventAssociationExtension::ASSOCIATION_KIND association.
 */
class VisitEventAssociationGeneratorExtension extends AbstractAssociationEntityGeneratorExtension
{
    public function supports(array $schema): bool
    {
        return
            $schema['class'] === TrackingVisitEvent::class
            && parent::supports($schema);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function getAssociationKind(): ?string
    {
        return VisitEventAssociationExtension::ASSOCIATION_KIND;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function getAssociationType(): string
    {
        return RelationType::MULTIPLE_MANY_TO_ONE;
    }
}
