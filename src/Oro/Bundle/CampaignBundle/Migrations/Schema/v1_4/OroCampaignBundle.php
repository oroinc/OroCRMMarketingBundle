<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\SecurityBundle\Migrations\Schema\UpdateOwnershipTypeQuery;

class OroCampaignBundle implements Migration
{
    #[\Override]
    public function up(Schema $schema, QueryBag $queries): void
    {
        $this->addOrganization($schema, 'orocrm_campaign');
        $this->addOrganization($schema, 'orocrm_campaign_email');

        //Add organization fields to ownership entity config
        $queries->addQuery(
            new UpdateOwnershipTypeQuery(
                'Oro\Bundle\CampaignBundle\Entity\Campaign',
                [
                    'organization_field_name'  => 'organization',
                    'organization_column_name' => 'organization_id'
                ]
            )
        );
        $queries->addQuery(
            new UpdateOwnershipTypeQuery(
                'Oro\Bundle\CampaignBundle\Entity\EmailCampaign',
                [
                    'organization_field_name'  => 'organization',
                    'organization_column_name' => 'organization_id'
                ]
            )
        );
    }

    private function addOrganization(Schema $schema, string $tableName): void
    {
        $table = $schema->getTable($tableName);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['organization_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
