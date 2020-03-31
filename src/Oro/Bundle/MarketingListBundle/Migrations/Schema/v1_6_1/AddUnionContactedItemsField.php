<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Schema\v1_6_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Modify orocrm_marketing_list table:
 * - add union_contacted_items boolean default true
 */
class AddUnionContactedItemsField implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('orocrm_marketing_list');
        if (!$table->hasColumn('union_contacted_items')) {
            $table->addColumn('union_contacted_items', 'boolean', ['notnull' => true, 'default' => true]);
        }
    }
}
