<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures\LoadMarketingActivityData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;

class MarketingActivityTest extends RestJsonApiTestCase
{
    use RolePermissionExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([
            LoadMarketingActivityData::class
        ]);
    }

    protected function postFixtureLoad()
    {
        $this->updateRolePermissions(
            'ROLE_ADMINISTRATOR',
            MarketingActivity::class,
            [
                'VIEW'   => AccessLevel::GLOBAL_LEVEL,
                'CREATE' => AccessLevel::GLOBAL_LEVEL,
                'EDIT'   => AccessLevel::GLOBAL_LEVEL,
                'DELETE' => AccessLevel::GLOBAL_LEVEL
            ]
        );
    }

    public function testGetList()
    {
        $marketingActivity1Id = $this->getReference('oro_marketing_activity_0')->getId();
        $marketingActivity2Id = $this->getReference('oro_marketing_activity_1')->getId();

        $response = $this->cget(
            ['entity' => 'marketingactivities'],
            ['filter[id]' => implode(',', [$marketingActivity1Id, $marketingActivity2Id])]
        );

        $this->assertResponseContains('cget_marketing_activity.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => 'marketingactivities', 'id' => '<toString(@oro_marketing_activity_1->id)>']
        );

        $this->assertResponseContains('get_marketing_activity.yml', $response);
    }

    public function testCreate()
    {
        $organizationId = $this->getReference('organization')->getId();
        $entityId = $this->getReference('test_entity_1')->getId();

        $response = $this->post(
            ['entity' => 'marketingactivities'],
            'create_marketing_activity_min.yml'
        );

        $marketingActivityId = (int)$this->getResourceId($response);

        /** @var MarketingActivity $marketingActivity */
        $marketingActivity = $this->getEntityManager()->find(MarketingActivity::class, $marketingActivityId);
        self::assertEquals($organizationId, $marketingActivity->getOwner()->getId());
        self::assertEquals(
            new \DateTime('2019-01-02 10:30:00', new \DateTimeZone('UTC')),
            $marketingActivity->getActionDate()
        );
        self::assertEquals('open', $marketingActivity->getType()->getId());
        self::assertEquals(TestActivity::class, $marketingActivity->getEntityClass());
        self::assertEquals($entityId, $marketingActivity->getEntityId());
    }

    public function testTryToCreateWithoutRequiredFields()
    {
        $response = $this->post(
            ['entity' => 'marketingactivities'],
            ['data' => ['type' => 'marketingactivities']],
            [],
            false
        );
        $this->assertResponseValidationErrors(
            [
                [
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/data/attributes/actionDate']
                ],
                [
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/data/relationships/marketingActivityType/data']
                ],
                [
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/data/relationships/entity/data']
                ]
            ],
            $response
        );
    }

    public function testUpdateEntityAssociation()
    {
        $marketingActivityId = $this->getReference('oro_marketing_activity_3')->getId();
        $entityId = $this->getReference('test_entity_2')->getId();

        $this->patch(
            ['entity' => 'marketingactivities', 'id' => (string)$marketingActivityId],
            [
                'data' => [
                    'type'          => 'marketingactivities',
                    'id'            => (string)$marketingActivityId,
                    'relationships' => [
                        'entity' => [
                            'data' => [
                                'type' => 'testactivities',
                                'id'   => (string)$entityId
                            ]
                        ]
                    ]
                ]
            ]
        );

        /** @var MarketingActivity $marketingActivity */
        $marketingActivity = $this->getEntityManager()->find(MarketingActivity::class, $marketingActivityId);
        self::assertEquals(TestActivity::class, $marketingActivity->getEntityClass());
        self::assertEquals($entityId, $marketingActivity->getEntityId());
    }

    public function testDelete()
    {
        $marketingActivityId = $this->getReference('oro_marketing_activity_3')->getId();

        $this->delete(
            ['entity' => 'marketingactivities', 'id' => (string)$marketingActivityId]
        );

        $marketingActivity = $this->getEntityManager()->find(MarketingActivity::class, $marketingActivityId);
        self::assertTrue(null === $marketingActivity);
    }
}
