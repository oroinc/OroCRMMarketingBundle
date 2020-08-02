<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Migration;

use Oro\Bundle\EntityConfigBundle\Migration\RemoveAssociationQuery;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;

/**
 * Removes visit identifier association from an entity and updates entity config.
 */
class RemoveVisitIdentifierAssociationQuery extends RemoveAssociationQuery
{
    public function __construct(string $targetEntityClass, string $targetTableName, bool $dropRelationColumnsAndTables)
    {
        $this->sourceEntityClass = TrackingVisit::class;
        $this->targetEntityClass = $targetEntityClass;
        $this->associationKind = IdentifierEventExtension::ASSOCIATION_KIND;
        $this->relationType = RelationType::MANY_TO_ONE;
        $this->sourceTableName = IdentifierEventExtension::VISIT_TABLE_NAME;
        $this->dropRelationColumnsAndTables = $dropRelationColumnsAndTables;
        $this->targetTableName = $targetTableName;
    }
}
