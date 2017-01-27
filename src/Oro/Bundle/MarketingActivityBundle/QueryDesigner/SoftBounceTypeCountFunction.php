<?php

namespace Oro\Bundle\MarketingActivityBundle\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

class SoftBounceTypeCountFunction extends AbstractTypeCountFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return MarketingActivity::TYPE_SOFT_BOUNCE;
    }
}
