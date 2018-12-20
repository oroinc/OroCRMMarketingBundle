<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Entity;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class MarketingActivityTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testAccessors()
    {
        $this->assertPropertyAccessors(new MarketingActivity(), [
            ['id', 42],
            ['owner', new Organization()],
            ['campaign', new Campaign()],
            ['entityId', 42],
            ['entityClass', 'some string'],
            ['relatedCampaignId', 42],
            ['relatedCampaignClass', 'some string'],
            ['details', 'some string'],
            ['actionDate', new \DateTime()]
        ]);
    }
}
