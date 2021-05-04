<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class EmailCampaignStatisticsTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            'id'                => ['id', 1],
            'createdAt'         => ['createdAt', new \DateTime()],
            'emailCampaign'     => ['emailCampaign', $this->createMock(EmailCampaign::class)],
            'marketingListItem' => ['marketingListItem', $this->createMock(MarketingListItem::class)],
            'openCount'         => ['openCount', 1],
            'clickCount'        => ['clickCount', 2],
            'bounceCount'       => ['bounceCount', 3],
            'abuseCount'        => ['abuseCount', 4],
            'unsubscribeCount'  => ['unsubscribeCount', 5],
        ];

        $entity = new EmailCampaignStatistics();
        self::assertPropertyAccessors($entity, $properties);
    }

    public function testPrePersist()
    {
        $entity = new EmailCampaignStatistics();
        $entity->prePersist();

        self::assertNotNull($entity->getCreatedAt());

        $existingCreatedAt = $entity->getCreatedAt();
        $entity->prePersist();
        self::assertNotSame($existingCreatedAt, $entity->getCreatedAt());
    }
}
