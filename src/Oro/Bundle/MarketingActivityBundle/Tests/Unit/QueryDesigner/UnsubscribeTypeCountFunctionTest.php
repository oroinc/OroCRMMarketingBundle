<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\UnsubscribeTypeCountFunction;

class UnsubscribeTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->function = new UnsubscribeTypeCountFunction();
        $this->type = ExtendHelper::buildEnumOptionId(
            MarketingActivity::TYPE_ENUM_CODE,
            MarketingActivity::TYPE_UNSUBSCRIBE
        );
    }
}
