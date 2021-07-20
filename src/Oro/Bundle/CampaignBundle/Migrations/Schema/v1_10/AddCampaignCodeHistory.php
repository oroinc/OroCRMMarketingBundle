<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCampaignCodeHistory implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation */
        $this->createOrocrmCampaignCodeHistoryTable($schema);

        /** Foreign keys generation */
        $this->addOrocrmCampaignCodeHistoryForeignKeys($schema);
    }

    /**
     * Create orocrm_campaign_code_history table
     */
    protected function createOrocrmCampaignCodeHistoryTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign_code_history');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => true]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['campaign_id'], 'IDX_E952F134F639F774', []);
        $table->addUniqueIndex(['code'], 'UNIQ_E952F13477153098');
    }

    /**
     * Add orocrm_campaign_code_history foreign keys.
     */
    protected function addOrocrmCampaignCodeHistoryForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign_code_history');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
