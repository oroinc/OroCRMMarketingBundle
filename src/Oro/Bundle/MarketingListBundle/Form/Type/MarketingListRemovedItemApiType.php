<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\Type\AbstractPatchableApiType;

class MarketingListRemovedItemApiType extends AbstractPatchableApiType
{
    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return MarketingListTypeRemovedItemType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'oro_marketing_list_removed_item_api';
    }
}
