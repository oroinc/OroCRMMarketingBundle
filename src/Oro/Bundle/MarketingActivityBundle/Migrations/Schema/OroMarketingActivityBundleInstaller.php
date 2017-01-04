<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MarketingActivityBundle\Migrations\Schema\v1_0;

class OroMarketingActivityInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $migration = new v1_0\OroMarketingActivityBundle();
        $migration->up($schema, $queries);
    }
}
