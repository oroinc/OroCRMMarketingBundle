<?php

namespace Oro\Bundle\MarketingActivityBundle\QueryDesigner;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

/**
 * SoftBounceTypeCountFunction class
 */
class SoftBounceTypeCountFunction extends AbstractTypeCountFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getType(): string
    {
        return ExtendHelper::buildEnumOptionId(
            MarketingActivity::TYPE_ENUM_CODE,
            MarketingActivity::TYPE_SOFT_BOUNCE
        );
    }
}
