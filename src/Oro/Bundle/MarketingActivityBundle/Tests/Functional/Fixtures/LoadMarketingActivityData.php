<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadCampaignData;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\TestFrameworkBundle\Entity\TestActivity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadMarketingActivityData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var array */
    private const DATA = [
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
            'relatedCampaign' => 'Campaign.Campaign2'
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
        $organization = $this->getReference('organization');
        $enumRepo = $manager->getRepository(ExtendHelper::buildEnumValueClassName('ma_type'));
        foreach (self::DATA as $key => $data) {
            $entity = new MarketingActivity();
            $entity->setOwner($organization)
                ->setCampaign($this->getReference($data['campaign']))
                ->setEntityId($this->getReference($data['entityClassReference'])->getId())
                ->setEntityClass(TestActivity::class)
                ->setActionDate(new \DateTime($data['actionDate']))
                ->setType($enumRepo->find($data['type']));
            if (isset($data['relatedCampaign'])) {
                $relatedCampaign = $this->getReference($data['relatedCampaign']);
                $entity->setRelatedCampaignClass(ClassUtils::getClass($relatedCampaign))
                    ->setRelatedCampaignId($relatedCampaign->getId());
            }

            $this->addReference('oro_marketing_activity_' . $key, $entity);
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
            LoadOrganization::class,
            LoadCampaignData::class,
            LoadTestEntityData::class
        ];
    }
}
