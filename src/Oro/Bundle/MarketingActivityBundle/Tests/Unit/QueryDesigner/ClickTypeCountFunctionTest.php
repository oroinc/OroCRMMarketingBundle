<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\ClickTypeCountFunction;

class ClickTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    protected function setUp(): void
    {
        $this->function = new ClickTypeCountFunction();
        $this->type = ExtendHelper::buildEnumOptionId(MarketingActivity::TYPE_ENUM_CODE, MarketingActivity::TYPE_CLICK);
    }
}
