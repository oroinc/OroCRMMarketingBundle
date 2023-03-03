<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class LoadCampaignData extends AbstractFixture implements DependentFixtureInterface
{
    /** @var array */
    private const DATA = [
        'Campaign.Campaign1' => [
            'name' => 'Campaign1',
            'code' => 'cmp1',
            'reportPeriod' => Campaign::PERIOD_DAILY
        ],
        'Campaign.Campaign2' => [
            'name' => 'Campaign2',
            'code' => 'cmp2',
            'reportPeriod' => Campaign::PERIOD_HOURLY,
            'reportRefreshDate' => '-1 day'
        ],
        'Campaign.Campaign3' => [
            'name' => 'Campaign3',
            'code' => 'cmp3',
            'reportPeriod' => Campaign::PERIOD_MONTHLY,
            'reportRefreshDate' => '-2 day'
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $organization = $this->getReference('organization');
        $user = $this->getReference('user');
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach (self::DATA as $reference => $data) {
            $entity = new Campaign();
            $entity->setOrganization($organization);
            $entity->setOwner($user);
            if (isset($data['reportRefreshDate'])) {
                $data['reportRefreshDate'] = new \DateTime($data['reportRefreshDate'], new \DateTimeZone('UTC'));
            }
            foreach ($data as $property => $value) {
                $propertyAccessor->setValue($entity, $property, $value);
            }

            $this->setReference($reference, $entity);
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
            LoadUser::class
        ];
    }
}
