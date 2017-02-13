<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\DataFixtures\SharedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;

class LoadMarketingActivityData implements DependentFixtureInterface, SharedFixtureInterface
{
    /**
     * Fixture reference repository
     *
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * {@inheritdoc}
     */
    public function setReferenceRepository(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * @var array
     */
    protected $data = [
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_1,
            'actionDate' => '2017-01-03T01:00:00+0000',
            'type' => 'send',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_1,
            'actionDate' => '2017-01-04T01:00:00+0000',
            'type' => 'open',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_1,
            'actionDate' => '2017-01-03T01:00:00+0000',
            'type' => 'click',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_2,
            'actionDate' => '2017-01-06T01:00:00+0000',
            'type' => 'soft_bounce',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_2,
            'actionDate' => '2017-01-06T01:00:00+0000',
            'type' => 'hard_bounce',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_2,
            'actionDate' => '2017-01-07T01:00:00+0000',
            'type' => 'unsubscribe',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_3,
            'actionDate' => '2017-01-30T01:00:00+0000',
            'type' => 'click',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_3,
            'actionDate' => '2017-01-31T01:00:00+0000',
            'type' => 'click',
        ],
        [
            'campaign' => 'Campaign.Campaign1',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_2,
            'actionDate' => '2017-01-02T01:00:00+0000',
            'type' => 'unsubscribe',
        ],
        [
            'campaign' => 'Campaign.Campaign2',
            'entityClassReference' => LoadTestEntityData::TEST_ENTITY_1,
            'actionDate' => '2017-01-03T01:00:00+0000',
            'type' => 'click',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $enumClass = ExtendHelper::buildEnumValueClassName('ma_type');
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        foreach ($this->data as $key => $data) {
            $entity = new MarketingActivity();
            $testEntity = $this->referenceRepository->getReference($data['entityClassReference']);
            $entity->setOwner($organization)
                ->setCampaign($this->referenceRepository->getReference($data['campaign']))
                ->setEntityId($testEntity->getId())
                ->setEntityClass(TestActivity::class)
                ->setActionDate(new \DateTime($data['actionDate']))
                ->setType($manager->getRepository($enumClass)->find($data['type']));

            $this->referenceRepository->addReference('oro_marketing_activity_' . $key, $entity);
            $manager->persist($entity);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignData',
            'Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures\LoadTestEntityData',
        ];
    }
}
