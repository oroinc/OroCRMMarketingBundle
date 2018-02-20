<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;

class UpdateTrackingVisitConfigs implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $fieldName = ExtendHelper::buildAssociationName(
            'Oro\Bundle\CampaignBundle\Entity\Campaign',
            VisitEventAssociationExtension::ASSOCIATION_KIND
        );

        $queries->addPostQuery(
            new UpdateEntityConfigFieldValueQuery(
                'Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent',
                $fieldName,
                'merge',
                'inverse_display',
                false
            )
        );
    }
}
