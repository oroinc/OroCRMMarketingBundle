<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroTrackerBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_tracking_event');
        $table->getColumn('url')
            ->setType(Type::getType(Types::TEXT))
            ->setLength(null);
        $table->getColumn('title')
            ->setType(Type::getType(Types::TEXT))
            ->setLength(null);
    }
}
