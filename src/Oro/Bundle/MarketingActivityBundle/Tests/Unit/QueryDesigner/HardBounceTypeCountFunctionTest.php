<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\HardBounceTypeCountFunction;

class HardBounceTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    protected function setUp(): void
    {
        $this->function = new HardBounceTypeCountFunction();
        $this->type = MarketingActivity::TYPE_HARD_BOUNCE;
    }
}
