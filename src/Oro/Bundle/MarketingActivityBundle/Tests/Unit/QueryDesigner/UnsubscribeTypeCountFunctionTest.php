<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\UnsubscribeTypeCountFunction;

class UnsubscribeTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    protected function setUp(): void
    {
        $this->function = new UnsubscribeTypeCountFunction();
        $this->type = MarketingActivity::TYPE_UNSUBSCRIBE;
    }
}
