<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigEntityValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateEntityConfigBundle implements Migration
{
    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::updateEntityConfigs($queries);
    }

    public static function updateEntityConfigs(QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigEntityValueQuery(
                'Oro\Bundle\MarketingListBundle\Entity\MarketingList',
                'form',
                'grid_name',
                'oro-marketing-list-select-grid'
            )
        );
    }
}
