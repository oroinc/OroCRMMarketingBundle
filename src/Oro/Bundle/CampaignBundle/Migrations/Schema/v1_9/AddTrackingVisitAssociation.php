<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareInterface;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtensionAwareTrait;

class AddTrackingVisitAssociation implements Migration, VisitEventAssociationExtensionAwareInterface
{
    use VisitEventAssociationExtensionAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->visitEventAssociationExtension->addVisitEventAssociation($schema, 'orocrm_campaign');
    }
}
