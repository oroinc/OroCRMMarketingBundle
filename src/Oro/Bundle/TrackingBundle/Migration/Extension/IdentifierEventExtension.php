<?php

namespace Oro\Bundle\TrackingBundle\Migration\Extension;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class IdentifierEventExtension implements ExtendExtensionAwareInterface
{
    const ASSOCIATION_KIND = 'identifier';
    const VISIT_TABLE_NAME = 'oro_tracking_visit';

    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * {@inheritdoc}
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * Adds the association between the target table and the visit table
     *
     * @param Schema $schema
     * @param string $targetTableName  Target entity table name
     * @param string $targetColumnName A column name is used to show related entity
     */
    public function addIdentifierAssociation(
        Schema $schema,
        $targetTableName,
        $targetColumnName = null
    ) {
        $visitTable   = $schema->getTable(self::VISIT_TABLE_NAME);
        $targetTable = $schema->getTable($targetTableName);

        if (empty($targetColumnName)) {
            $primaryKeyColumns = $targetTable->getPrimaryKeyColumns();
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
            $targetColumnName
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
    public function hasIdentifierAssociation(Schema $schema, $targetTableName)
    {
        $visitTable = $schema->getTable(self::VISIT_TABLE_NAME);
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
