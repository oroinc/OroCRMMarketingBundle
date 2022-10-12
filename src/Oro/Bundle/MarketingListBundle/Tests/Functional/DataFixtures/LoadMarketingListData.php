<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadMarketingListData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    public const MARKETING_LIST_1 = 'marketing_list_1';

    public function load(ObjectManager $manager): void
    {
        $type = $manager->getRepository(MarketingListType::class)
            ->find(MarketingListType::TYPE_DYNAMIC);

        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);

        $entity = new MarketingList();
        $entity
            ->setType($type)
            ->setName(self::MARKETING_LIST_1)
            ->setEntity(Contact::class)
            ->setOrganization($organization);

        $manager->persist($entity);
        $manager->flush($entity);

        $this->addReference(self::MARKETING_LIST_1, $entity);
    }

    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }
}
