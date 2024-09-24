<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Environment;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\UserBundle\Entity\User;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;

    public function __construct(TestEntityNameResolverDataLoaderInterface $innerDataLoader)
    {
        $this->innerDataLoader = $innerDataLoader;
    }

    #[\Override]
    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (MarketingList::class === $entityClass) {
            $marketingList = new MarketingList();
            $marketingList->setOrganization($repository->getReference('organization'));
            $marketingList->setOwner($repository->getReference('user'));
            $marketingList->setType(
                $em->getRepository(MarketingListType::class)->find(MarketingListType::TYPE_DYNAMIC)
            );
            $marketingList->setEntity(User::class);
            $marketingList->setName('Test Marketing List');
            $repository->setReference('marketingList', $marketingList);
            $em->persist($marketingList);
            $em->flush();

            return ['marketingList'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    #[\Override]
    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (MarketingList::class === $entityClass) {
            return 'Test Marketing List';
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }
}
