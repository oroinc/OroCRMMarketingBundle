<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\InstallerBundle\Migration\UpdateTableFieldQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MigrateRelations implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(new UpdateTableFieldQuery(
            'orocrm_marketing_list',
            'entity',
            'OroCRM',
            'Oro'
        ));
        $queries->addQuery(new UpdateTableFieldQuery(
            'orocrm_marketing_list_type',
            'label',
            'orocrm',
            'oro',
            'name'
        ));
    }
}
