<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadMarketingListData extends AbstractFixture implements DependentFixtureInterface
{
    public const MARKETING_LIST_NAME = 'list_name';

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $entity = new MarketingList();
        $entity->setType($manager->getRepository(MarketingListType::class)->find(MarketingListType::TYPE_DYNAMIC));
        $entity->setName(self::MARKETING_LIST_NAME);
        $entity->setEntity(Contact::class);
        $entity->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $manager->persist($entity);
        $manager->flush($entity);
        $this->addReference(self::MARKETING_LIST_NAME, $entity);
    }
}
