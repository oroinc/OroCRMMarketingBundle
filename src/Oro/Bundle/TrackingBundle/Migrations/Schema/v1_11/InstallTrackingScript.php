<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InstallTrackingScript implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /**
         * Tracking script installation (copying `tracking.php` into web folder) replaced with assets.
         * See `Oro/Bundle/TrackingBundle/Resources/config/oro/app.yml`.
         */
    }
}
