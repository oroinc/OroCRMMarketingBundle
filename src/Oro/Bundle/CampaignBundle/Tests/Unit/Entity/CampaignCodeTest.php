<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCode;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CampaignCodeTest extends \PHPUnit_Framework_TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new CampaignCode(), [
            ['id', 42],
            ['campaign', new Campaign()],
            ['code', 'some string']
        ]);
    }
}
