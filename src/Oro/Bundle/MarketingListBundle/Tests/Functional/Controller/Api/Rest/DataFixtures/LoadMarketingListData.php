<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMarketingListData extends AbstractFixture implements ContainerAwareInterface
{
    const MARKETING_LIST_NAME = 'list_name';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $type = $manager->getRepository(MarketingListType::class)
            ->find(MarketingListType::TYPE_DYNAMIC);

        $entity = new MarketingList();
        $entity
            ->setType($type)
            ->setName(self::MARKETING_LIST_NAME)
            ->setEntity(Contact::class)
            ->setOrganization($manager->getRepository(Organization::class)->getFirst());

        $manager->persist($entity);
        $manager->flush($entity);

        $this->addReference(self::MARKETING_LIST_NAME, $entity);
    }
}
