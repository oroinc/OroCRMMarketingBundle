<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CampaignCodeHistoryTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new CampaignCodeHistory(), [
            ['id', 42],
            ['campaign', new Campaign()],
            ['code', 'some string']
        ]);
    }
}
