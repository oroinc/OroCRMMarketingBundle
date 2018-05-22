<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\Type\AbstractPatchableApiType;

class MarketingListUnsubscribedItemApiType extends AbstractPatchableApiType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return MarketingListTypeUnsubscribedItemType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_marketing_list_unsubscribed_item_api';
    }
}
