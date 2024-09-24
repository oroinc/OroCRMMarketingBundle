<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class LoadTrackingWebsiteData extends AbstractFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [LoadUser::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $website = new TrackingWebsite();
        $website->setName('delete');
        $website->setIdentifier('delete');
        $website->setUrl('http://domain.com');
        $website->setOwner($this->getReference(LoadUser::USER));
        $this->setReference('website', $website);
        $manager->persist($website);
        $manager->flush();
    }
}
