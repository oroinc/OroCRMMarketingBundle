<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\TransportSettings;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class EmailCampaignTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            'id'                => ['id', 1],
            'name'              => ['name', 'test'],
            'description'       => ['description', 'test'],
            'campaign'          => ['campaign', $this->createMock(Campaign::class)],
            'sent'              => ['sent', new \DateTime()],
            'sentAt'            => ['sentAt', true],
            'schedule'          => ['schedule', EmailCampaign::SCHEDULE_DEFERRED],
            'scheduledFor'      => ['scheduledFor', new \DateTime()],
            'marketingList'     => ['marketingList', $this->createMock(MarketingList::class)],
            'owner'             => ['owner', $this->createMock(User::class)],
            'updatedAt'         => ['updatedAt', new \DateTime()],
            'createdAt'         => ['createdAt', new \DateTime()],
            'senderEmail'       => ['senderEmail', 'test@test.com'],
            'senderName'        => ['senderName', 'name'],
            'transport'         => ['transport', 'transport'],
            'transportSettings' => ['transportSettings', $this->getMockForAbstractClass(TransportSettings::class)],
        ];

        $entity = new EmailCampaign();
        self::assertPropertyAccessors($entity, $properties);
    }

    public function testPrePersist()
    {
        $entity = new EmailCampaign();
        $entity->prePersist();

        $this->assertNotNull($entity->getCreatedAt());
        $this->assertNotNull($entity->getUpdatedAt());
        $this->assertEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
        $this->assertNotSame($entity->getCreatedAt(), $entity->getUpdatedAt());

        $existingCreatedAt = $entity->getCreatedAt();
        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->prePersist();
        self::assertNotSame($existingCreatedAt, $entity->getCreatedAt());
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
        self::assertEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
        self::assertNotSame($entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $entity = new EmailCampaign();
        $entity->preUpdate();

        $this->assertNotNull($entity->getUpdatedAt());

        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->preUpdate();
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
    }

    public function testUnknownSchedule()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Schedule type unknown is not know. Known types are manual, deferred');

        $entity = new EmailCampaign();
        $entity->setSchedule('unknown');
    }

    public function testGetEntityName()
    {
        $entity = new EmailCampaign();
        self::assertNull($entity->getEntityName());

        $marketingList = new MarketingList();
        $marketingList->setEntity(\stdClass::class);
        $entity->setMarketingList($marketingList);
        self::assertSame($marketingList->getEntity(), $entity->getEntityName());
    }
}
