<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tools;

use Oro\Bundle\EntityExtendBundle\Tools\GeneratorExtensions\AbstractAssociationEntityGeneratorExtension;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;

/**
 * Generates PHP code for many-to-one IdentifierEventExtension::ASSOCIATION_KIND association.
 */
class IdentifierVisitGeneratorExtension extends AbstractAssociationEntityGeneratorExtension
{
    public function supports(array $schema): bool
    {
        return
            $schema['class'] === TrackingVisit::class
            && parent::supports($schema);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function getAssociationKind(): ?string
    {
        return IdentifierEventExtension::ASSOCIATION_KIND;
    }
}
