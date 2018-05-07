<?php

namespace Oro\Bundle\TrackingBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class UpdateFormTypeConfigsToFQCN implements Migration
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
                OroResizeableRichTextType::class,
                'oro_resizeable_rich_text'
            )
        );

        $queries->addQuery(
            new UpdateEntityConfigFieldValueQuery(
                TrackingWebsite::class,
                'channel',
                'form',
                'form_type',
                ChannelSelectType::class,
                'oro_channel_select_type'
            )
        );
    }
}
