<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class UpdateFormTypeForExtendDescription implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                TrackingWebsite::class,
                'extend_description',
                'form',
                'type',
                OroResizeableRichTextType::class
            )
        );
    }
}
