<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\Type\AbstractPatchableApiType;

class MarketingListRemovedItemApiType extends AbstractPatchableApiType
{
    #[\Override]
    public function getParent(): ?string
    {
        return MarketingListTypeRemovedItemType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_marketing_list_removed_item_api';
    }
}
