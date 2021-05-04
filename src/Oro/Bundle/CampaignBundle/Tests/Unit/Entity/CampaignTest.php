<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class CampaignTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            'id'           => ['id', 1],
            'name'         => ['name', 'Some Name'],
            'code'         => ['code', '123-abc'],
            'startDate'    => ['startDate', new \DateTime()],
            'endDate'      => ['endDate', new \DateTime()],
            'description'  => ['description', 'some description'],
            'budget'       => ['budget', 10.44],
            'owner'        => ['owner', $this->createMock(User::class)],
            'organization' => ['organization', $this->createMock(Organization::class)],
        ];

        $entity = new Campaign();
        self::assertPropertyAccessors($entity, $properties);
    }

    public function testPrePersist()
    {
        $entity = new Campaign();
        $entity->prePersist();

        self::assertNotNull($entity->getCreatedAt());
        self::assertNotNull($entity->getUpdatedAt());
        self::assertEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
        self::assertNotSame($entity->getCreatedAt(), $entity->getUpdatedAt());

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
        $entity = new Campaign();
        $entity->preUpdate();

        self::assertNotNull($entity->getUpdatedAt());

        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->preUpdate();
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
    }

    public function testCombinedName()
    {
        $entity = new Campaign();
        $entity->setName('test name');
        $entity->setCode('test_code');
        self::assertNull($entity->getCombinedName());

        $entity->prePersist();
        self::assertEquals('test name (test_code)', $entity->getCombinedName());

        $entity->setCode('new_code');
        $entity->preUpdate();
        self::assertEquals('test name (new_code)', $entity->getCombinedName());
    }
}
