<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMarketingActivityBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createOroMarketingActivityTable($schema);
        $this->createOroMarketingActivityTypeTable($schema);
        $this->addOroMarketingActivityForeignKeys($schema);
    }

    /**
     * Create orocrm_marketing_activity table
     *
     * @param Schema $schema
     */
    protected function createOroMarketingActivityTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_marketing_activity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('type_id', 'integer', ['notnull' => false]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('entity_id', 'integer', ['notnull' => true]);
        $table->addColumn('entity_class', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('related_campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_campaign_class', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('details', 'text', ['notnull' => false]);
        $table->addColumn('action_date', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addIndex(['owner_id'], 'IDX_8A01A8357E3C61F9', []);
        $table->addIndex(['campaign_id'], 'IDX_8A01A835F639F774', []);
        $table->addIndex(['entity_id', 'entity_class'], 'IDX_MARKETING_ACTIVITY_ENTITY', []);
        $table->addIndex(
            ['related_campaign_id', 'related_campaign_class'],
            'IDX_MARKETING_ACTIVITY_RELATED_CAMPAIGN',
            []
        );
        $table->addIndex(['action_date'], 'IDX_MARKETING_ACTIVITY_ACTION_DATE', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orocrm_marketing_activity_type table
     *
     * @param Schema $schema
     */
    protected function createOroMarketingActivityTypeTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_marketing_activity_type');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 32, 'notnull' => true]);
        $table->addColumn('label', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('template', 'string', ['length' => 255, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add orocrm_marketing_activity foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroMarketingActivityForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_marketing_activity');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_marketing_activity_type'),
            ['type_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
    }
}
