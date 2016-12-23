<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class InstallTrackingScript implements Migration, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new InstallTrackingScriptQuery($this->container->getParameter('oro_tracking.web_root'))
        );
    }
}
