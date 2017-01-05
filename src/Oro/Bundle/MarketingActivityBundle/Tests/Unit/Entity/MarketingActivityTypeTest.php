<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Entity;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivityType;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MarketingActivityTypeTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new MarketingActivityType(), [
            ['id', 42],
            ['name', 'some string'],
            ['label', 'some string'],
            ['template', 'some string']
        ]);
    }
}
