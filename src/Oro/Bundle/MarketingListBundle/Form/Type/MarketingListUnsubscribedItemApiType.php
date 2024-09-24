<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\Type\AbstractPatchableApiType;

class MarketingListUnsubscribedItemApiType extends AbstractPatchableApiType
{
    #[\Override]
    public function getParent(): ?string
    {
        return MarketingListTypeUnsubscribedItemType::class;
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_marketing_list_unsubscribed_item_api';
    }
}
