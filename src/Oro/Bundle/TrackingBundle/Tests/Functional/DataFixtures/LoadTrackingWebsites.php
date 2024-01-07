<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

class LoadTrackingWebsites extends AbstractFixture implements DependentFixtureInterface
{
    public const TRACKING_WEBSITE = 'oro_tracking.website1';

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class, LoadUser::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $website = new TrackingWebsite();
        $website->setIdentifier(self::TRACKING_WEBSITE);
        $website->setName(self::TRACKING_WEBSITE);
        $website->setOrganization($this->getReference(LoadOrganization::ORGANIZATION));
        $website->setOwner($this->getReference(LoadUser::USER));
        $website->setUrl('http://localhost');
        $manager->persist($website);
        $manager->flush($website);
        $this->setReference(self::TRACKING_WEBSITE, $website);
    }
}
