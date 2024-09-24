<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroMarketingListBundleInstaller implements Installation
{
    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_6_1';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOrocrmMarketingListTypeTable($schema);
        $this->createOrocrmMarketingListTable($schema);
        $this->createOrocrmMlItemUnsTable($schema);
        $this->createOrocrmMarketingListItemTable($schema);
        $this->createOrocrmMlItemRmTable($schema);

        /** Foreign keys generation **/
        $this->addOrocrmMarketingListForeignKeys($schema);
        $this->addOrocrmMlItemUnsForeignKeys($schema);
        $this->addOrocrmMarketingListItemForeignKeys($schema);
        $this->addOrocrmMlItemRmForeignKeys($schema);
    }

    /**
     * Create oro_marketing_list_type table
     */
    private function createOrocrmMarketingListTypeTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_marketing_list_type');
        $table->addColumn('name', 'string', ['length' => 32]);
        $table->addColumn('label', 'string', ['length' => 255]);
        $table->setPrimaryKey(['name']);
        $table->addUniqueIndex(['label'], 'uniq_143b81a8ea750e8');
    }

    /**
     * Create oro_marketing_list table
     */
    private function createOrocrmMarketingListTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_marketing_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('type', 'string', ['length' => 32]);
        $table->addColumn('segment_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('entity', 'string', ['length' => 255]);
        $table->addColumn('last_run', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('union_contacted_items', 'boolean', ['notnull' => true, 'default' => true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'idx_3acc3ba7e3c61f9');
        $table->addIndex(['segment_id'], 'idx_3acc3badb296aad');
        $table->addIndex(['type'], 'idx_3acc3ba8cde5729');
        $table->addIndex(['organization_id'], 'idx_3acc3ba32c8a3de');
        $table->addUniqueIndex(['name'], 'uniq_3acc3ba5e237e06');
    }

    /**
     * Create oro_ml_item_uns table
     */
    private function createOrocrmMlItemUnsTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_ml_item_uns');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer');
        $table->addColumn('entity_id', 'integer');
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['entity_id', 'marketing_list_id'], 'orocrm_ml_list_ent_uns_unq');
        $table->addIndex(['marketing_list_id'], 'idx_ceb0306896434d04');
    }

    /**
     * Create oro_marketing_list_item table
     */
    private function createOrocrmMarketingListItemTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_marketing_list_item');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer');
        $table->addColumn('entity_id', 'integer');
        $table->addColumn('contacted_times', 'integer', ['notnull' => false]);
        $table->addColumn('last_contacted_at', 'datetime', ['notnull' => false, 'comment' => '(DC2Type:datetime)']);
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['marketing_list_id'], 'idx_87fef39f96434d04');
        $table->addUniqueIndex(['entity_id', 'marketing_list_id'], 'orocrm_ml_list_ent_unq');
    }

    /**
     * Create oro_ml_item_rm table
     */
    private function createOrocrmMlItemRmTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_ml_item_rm');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('marketing_list_id', 'integer');
        $table->addColumn('entity_id', 'integer');
        $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['marketing_list_id'], 'idx_8f6405f96434d04');
        $table->addUniqueIndex(['entity_id', 'marketing_list_id'], 'orocrm_ml_list_ent_rm_unq');
    }

    /**
     * Add oro_marketing_list foreign keys.
     */
    private function addOrocrmMarketingListForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_marketing_list');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list_type'),
            ['type'],
            ['name'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_segment'),
            ['segment_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }

    /**
     * Add oro_ml_item_uns foreign keys.
     */
    private function addOrocrmMlItemUnsForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_ml_item_uns');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_marketing_list_item foreign keys.
     */
    private function addOrocrmMarketingListItemForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_marketing_list_item');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_ml_item_rm foreign keys.
     */
    private function addOrocrmMlItemRmForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_ml_item_rm');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_list'),
            ['marketing_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
