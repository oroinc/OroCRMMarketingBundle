<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\SendTypeCountFunction;

class SendTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    protected function setUp(): void
    {
        $this->function = new SendTypeCountFunction();
        $this->type = MarketingActivity::TYPE_SEND;
    }
}
