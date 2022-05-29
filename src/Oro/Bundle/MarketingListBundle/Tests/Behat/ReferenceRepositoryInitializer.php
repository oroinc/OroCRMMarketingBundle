<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Behat;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\TestFrameworkBundle\Behat\Isolation\ReferenceRepositoryInitializerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\DataFixtures\Collection;

class ReferenceRepositoryInitializer implements ReferenceRepositoryInitializerInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(ManagerRegistry $doctrine, Collection $referenceRepository): void
    {
        /** @var MarketingListType[]|array $types */
        $types = $doctrine->getRepository(MarketingListType::class)->findAll();
        foreach ($types as $type) {
            $referenceRepository->set(sprintf('marketing_list_%s_type', strtolower($type->getName())), $type);
        }
    }
}
