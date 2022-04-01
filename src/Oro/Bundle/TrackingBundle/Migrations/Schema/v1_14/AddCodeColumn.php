<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddCodeColumn implements Migration
{
    public function up(Schema $schema, QueryBag $queries): void
    {
        if ($schema->hasTable('oro_tracking_visit')) {
            $table = $schema->getTable('oro_tracking_visit');
            $table->addColumn('code', 'string', ['notnull' => false, 'length' => 255]);
        }

        if ($schema->hasTable('oro_tracking_visit_event')) {
            $table = $schema->getTable('oro_tracking_visit_event');
            $table->addColumn('code', 'string', ['notnull' => false, 'length' => 255]);
        }
    }
}
