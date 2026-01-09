<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

/**
 * Provides an ability to create tracking visit event related associations.
 */
class VisitEventAssociationExtension implements ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    public const ASSOCIATION_KIND = 'association';
    public const VISIT_EVENT_TABLE_NAME = 'oro_tracking_visit_event';

    /**
     * Adds the association between the target table and the visit event table
     *
     * @param Schema $schema
     * @param string $targetTableName  Target entity table name
     * @param string $targetColumnName A column name is used to show related entity
     * @param array  $options          Entity config options. [scope => [name => value, ...], ...]
     */
    public function addVisitEventAssociation(
        Schema $schema,
        $targetTableName,
        $targetColumnName = null,
        array $options = []
    ) {
        $visitTable   = $schema->getTable(self::VISIT_EVENT_TABLE_NAME);
        $targetTable = $schema->getTable($targetTableName);

        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKey()->getColumns();
            $targetColumnName  = array_shift($primaryKeyColumns);
        }

        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName),
            self::ASSOCIATION_KIND
        );

        $this->extendExtension->addManyToOneRelation(
            $schema,
            $visitTable,
            $associationName,
            $targetTable,
            $targetColumnName,
            $options
        );
    }

    /**
     * @param Schema $schema
     * @param string $targetTableName
     *
     * @return bool
     *
     * @throws SchemaException if valid primary key does not exist
     */
    public function hasVisitEventAssociation(Schema $schema, $targetTableName)
    {
        $visitTable = $schema->getTable(self::VISIT_EVENT_TABLE_NAME);
        $targetTable  = $schema->getTable($targetTableName);

        $associationName = ExtendHelper::buildAssociationName(
            $this->extendExtension->getEntityClassByTableName($targetTableName),
            self::ASSOCIATION_KIND
        );

        if (!$targetTable->hasPrimaryKey()) {
            throw new SchemaException(
                sprintf('The table "%s" must have a primary key.', $targetTable->getName())
            );
        }
        $primaryKeyColumns = $targetTable->getPrimaryKey()->getColumns();
        if (count($primaryKeyColumns) !== 1) {
            throw new SchemaException(
                sprintf('A primary key of "%s" table must include only one column.', $targetTable->getName())
            );
        }

        $primaryKeyColumnName = array_pop($primaryKeyColumns);

        $nameGenerator = $this->extendExtension->getNameGenerator();
        $selfColumnName = $nameGenerator->generateRelationColumnName(
            $associationName,
            '_' . $primaryKeyColumnName
        );

        return $visitTable->hasColumn($selfColumnName);
    }
}
