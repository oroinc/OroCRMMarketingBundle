<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroMarketingActivityBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    use ExtendExtensionAwareTrait;

    #[\Override]
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOroMarketingActivityTable($schema);

        /** Foreign keys generation **/
        $this->addOroMarketingActivityForeignKeys($schema);

        $this->addMarketingActivityTypeField($schema);
    }

    private function createOroMarketingActivityTable(Schema $schema): void
    {
        $table = $schema->createTable('orocrm_marketing_activity');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('entity_id', 'integer', ['notnull' => true]);
        $table->addColumn('entity_class', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('related_campaign_id', 'integer', ['notnull' => false]);
        $table->addColumn('related_campaign_class', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('details', 'text', ['notnull' => false]);
        $table->addColumn('action_date', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['campaign_id'], 'IDX_8A01A835F639F774');
        $table->addIndex(['entity_id', 'entity_class'], 'IDX_MARKETING_ACTIVITY_ENTITY');
        $table->addIndex(['related_campaign_id', 'related_campaign_class'], 'IDX_MARKETING_ACTIVITY_RELATED_CAMPAIGN');
        $table->addIndex(['action_date'], 'IDX_MARKETING_ACTIVITY_ACTION_DATE');
    }

    private function addOroMarketingActivityForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('orocrm_marketing_activity');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
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

    private function addMarketingActivityTypeField(Schema $schema): void
    {
        $this->extendExtension->addEnumField(
            $schema,
            'orocrm_marketing_activity',
            'type',
            'ma_type',
            false,
            true,
            [
                'extend' => ['owner' => ExtendScope::OWNER_SYSTEM],
                'datagrid' => ['is_visible' => DatagridScope::IS_VISIBLE_TRUE]
            ]
        );
    }
}
