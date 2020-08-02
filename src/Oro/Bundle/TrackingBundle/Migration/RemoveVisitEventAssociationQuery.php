<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Migration;

use Oro\Bundle\EntityConfigBundle\Migration\RemoveAssociationQuery;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;

/**
 * Removes visit event association from an entity and updates entity config.
 */
class RemoveVisitEventAssociationQuery extends RemoveAssociationQuery
{
    public function __construct(string $targetEntityClass, string $targetTableName, bool $dropRelationColumnsAndTables)
    {
        $this->sourceEntityClass = TrackingVisitEvent::class;
        $this->targetEntityClass = $targetEntityClass;
        $this->associationKind = VisitEventAssociationExtension::ASSOCIATION_KIND;
        $this->relationType = RelationType::MANY_TO_ONE;
        $this->sourceTableName = VisitEventAssociationExtension::VISIT_EVENT_TABLE_NAME;
        $this->dropRelationColumnsAndTables = $dropRelationColumnsAndTables;
        $this->targetTableName = $targetTableName;
    }
}
