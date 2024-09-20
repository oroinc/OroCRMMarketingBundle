<?php

namespace Oro\Bundle\MarketingActivityBundle\QueryDesigner;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

/**
 * UnsubscribeTypeCountFunction class
 */
class UnsubscribeTypeCountFunction extends AbstractTypeCountFunction
{
    /**
     * {@inheritdoc}
     */
    protected function getType(): string
    {
        return ExtendHelper::buildEnumOptionId(
            MarketingActivity::TYPE_ENUM_CODE,
            MarketingActivity::TYPE_UNSUBSCRIBE
        );
    }
}
