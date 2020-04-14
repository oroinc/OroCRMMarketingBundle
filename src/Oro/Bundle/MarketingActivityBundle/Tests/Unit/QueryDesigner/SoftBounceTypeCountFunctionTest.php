<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\SoftBounceTypeCountFunction;

class SoftBounceTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    protected function setUp(): void
    {
        $this->function = new SoftBounceTypeCountFunction();
        $this->type = MarketingActivity::TYPE_SOFT_BOUNCE;
    }
}
