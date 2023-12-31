<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityBundle\EntityConfig\DatagridScope;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class OroTrackingBundleInstaller implements Installation
{
    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion(): string
    {
        return 'v1_14';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries): void
    {
        /** Tables generation **/
        $this->createOroTrackingDataTable($schema);
        $this->createOroTrackingEventTable($schema);
        $this->createOroTrackingWebsiteTable($schema);
        $this->createOroTrackingVisitTable($schema);
        $this->createOroTrackingVisitEventTable($schema);
        $this->createOroTrackingEventDictionaryTable($schema);
        $this->createOroTrackingUniqueVisitTable($schema);

        /** Foreign keys generation **/
        $this->addOroTrackingDataForeignKeys($schema);
        $this->addOroTrackingEventForeignKeys($schema);
        $this->addOroTrackingWebsiteForeignKeys($schema);
        $this->addOroTrackingVisitEventForeignKeys($schema);
        $this->addOroTrackingEventDictionaryForeignKeys($schema);
        $this->addOroTrackingVisitForeignKeys($schema);
        $this->addOroTrackingUniqueVisitForeignKeys($schema);
    }

    /**
     * Create oro_tracking_data table
     */
    private function createOroTrackingDataTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_data');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('event_id', 'integer', ['notnull' => false]);
        $table->addColumn('data', 'text');
        $table->addColumn('created_at', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['event_id'], 'uniq_b3cfdd2d71f7e88b');
    }

    /**
     * Create oro_tracking_event table
     */
    private function createOroTrackingEventTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_event');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('value', 'float', ['notnull' => false]);
        $table->addColumn('user_identifier', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('logged_at', 'datetime');
        $table->addColumn('url', 'text');
        $table->addColumn('title', 'text', ['notnull' => false]);
        $table->addColumn('code', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('parsed', 'boolean', ['default' => '0']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['logged_at'], 'event_loggedat_idx');
        $table->addIndex(['code'], 'code_idx');
        $table->addIndex(['name'], 'event_name_idx');
        $table->addIndex(['website_id'], 'idx_aad45a1e18f45c82');
        $table->addIndex(['created_at'], 'event_createdAt_idx');
        $table->addIndex(['parsed'], 'event_parsed_idx');
    }

    /**
     * Create oro_tracking_website table
     */
    private function createOroTrackingWebsiteTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_website');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('identifier', 'string', ['length' => 255]);
        $table->addColumn('url', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn(
            'extend_description',
            'text',
            [
                'oro_options' => [
                    'extend'    => ['is_extend' => true, 'owner' => ExtendScope::OWNER_CUSTOM],
                    'datagrid'  => ['is_visible' => DatagridScope::IS_VISIBLE_FALSE],
                    'merge'     => ['display' => true],
                    'dataaudit' => ['auditable' => true],
                    'form'      => ['type' => OroResizeableRichTextType::class],
                    'view'      => ['type' => 'html'],
                ]
            ]
        );
        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_owner_id'], 'idx_190388989eb185f9');
        $table->addIndex(['organization_id'], 'IDX_1903889832C8A3DE');
        $table->addUniqueIndex(['identifier'], 'uniq_19038898772e836a');
    }

    /**
     * Add oro_tracking_data foreign keys.
     */
    private function addOroTrackingDataForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_data');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_event'),
            ['event_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tracking_event foreign keys.
     */
    private function addOroTrackingEventForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_event');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_website'),
            ['website_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tracking_website foreign keys.
     */
    private function addOroTrackingWebsiteForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_website');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_user'),
            ['user_owner_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'SET NULL']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }

    /**
     * Create oro_tracking_visit table
     */
    private function createOroTrackingVisitTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_visit');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('visitor_uid', 'string', ['length' => 255]);
        $table->addColumn('ip', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client_type', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('client_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('os', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('os_version', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('desktop', 'boolean', ['notnull' => false]);
        $table->addColumn('mobile', 'boolean', ['notnull' => false]);
        $table->addColumn('bot', 'boolean', ['notnull' => false]);
        $table->addColumn('user_identifier', 'string', ['length' => 255]);
        $table->addColumn('first_action_time', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('last_action_time', 'datetime', ['comment' => '(DC2Type:datetime)']);
        $table->addColumn('parsing_count', 'integer', ['default' => '0']);
        $table->addColumn('parsed_uid', 'integer', ['default' => '0']);
        $table->addColumn('identifier_detected', 'boolean', ['default' => '0']);
        $table->addColumn('code', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_id'], 'idx_d204b98018f45c82');
        $table->addIndex(['visitor_uid'], 'visit_visitorUid_idx');
        $table->addIndex(['user_identifier'], 'visit_userIdentifier_idx');
        $table->addIndex(['website_id', 'first_action_time'], 'website_first_action_time_idx');
    }

    /**
     * Create oro_tracking_visit_event table
     */
    private function createOroTrackingVisitEventTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_visit_event');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('event_id', 'integer', ['notnull' => false]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('visit_id', 'integer', ['notnull' => false]);
        $table->addColumn('web_event_id', 'integer', ['notnull' => false]);
        $table->addColumn('parsing_count', 'integer', ['default' => '0']);
        $table->addColumn('code', 'string', ['notnull' => false, 'length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['event_id'], 'idx_b39eee8f71f7e88b');
        $table->addIndex(['visit_id'], 'idx_b39eee8f75fa0ff2');
        $table->addIndex(['website_id'], 'idx_b39eeebf18f45c82');
        $table->addUniqueIndex(['web_event_id'], 'uniq_b39eee8f66a8f966');
    }

    /**
     * Create oro_tracking_event_lib table
     */
    private function createOroTrackingEventDictionaryTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_event_dictionary');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_id'], 'idx_5c3b076318f45c82');
    }

    /**
     * Create oro_tracking_unique_visit table
     */
    private function createOroTrackingUniqueVisitTable(Schema $schema): void
    {
        $table = $schema->createTable('oro_tracking_unique_visit');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('website_id', 'integer', ['notnull' => false]);
        $table->addColumn('visit_count', 'integer');
        $table->addColumn('user_identifier', 'string', ['length' => 32]);
        $table->addColumn('action_date', 'date');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['website_id', 'action_date'], 'uvisit_action_date_idx');
        $table->addIndex(['user_identifier', 'action_date'], 'uvisit_user_by_date_idx');
    }

    /**
     * Add oro_tracking_visit_event foreign keys.
     */
    private function addOroTrackingVisitEventForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_visit_event');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_event_dictionary'),
            ['event_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_visit'),
            ['visit_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_event'),
            ['web_event_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_website'),
            ['website_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tracking_event_dictionary foreign keys.
     */
    private function addOroTrackingEventDictionaryForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_event_dictionary');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_website'),
            ['website_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tracking_visit foreign keys.
     */
    private function addOroTrackingVisitForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_visit');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_website'),
            ['website_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add oro_tracking_unique_visit foreign keys.
     */
    private function addOroTrackingUniqueVisitForeignKeys(Schema $schema): void
    {
        $table = $schema->getTable('oro_tracking_unique_visit');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_tracking_website'),
            ['website_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
