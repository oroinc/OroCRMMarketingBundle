<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCampaignBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation */
        $this->createOrocrmCampaignCodeTable($schema);

        /** Foreign keys generation */
        $this->addOrocrmCampaignCodeForeignKeys($schema);
    }

    /**
     * Create orocrm_campaign_code table
     *
     * @param Schema $schema
     */
    protected function createOrocrmCampaignCodeTable(Schema $schema)
    {
        $table = $schema->createTable('orocrm_campaign_code');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['campaign_id'], 'IDX_8F104FC2F639F774', []);
        $table->addUniqueIndex(['code'], 'UNIQ_8F104FC277153098');
    }

    /**
     * Add orocrm_campaign_code foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOrocrmCampaignCodeForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orocrm_campaign_code');
        $table->addForeignKeyConstraint(
            $schema->getTable('orocrm_campaign'),
            ['campaign_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
