<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\QueryDesigner\OpenTypeCountFunction;

class OpenTypeCountFunctionTest extends AbstractTypeCountFunctionTestCase
{
    protected function setUp(): void
    {
        $this->function = new OpenTypeCountFunction();
        $this->type = MarketingActivity::TYPE_OPEN;
    }
}
