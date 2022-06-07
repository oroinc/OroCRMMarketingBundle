<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Controller\Api\Rest\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadTrackingWebsiteData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $owner = $manager->getRepository(User::class)->findOneBy(['username' => 'admin']);
        if (!$owner) {
            return;
        }

        $website = new TrackingWebsite();
        $website
            ->setName('delete')
            ->setIdentifier('delete')
            ->setUrl('http://domain.com')
            ->setOwner($owner);

        $manager->persist($website);
        $manager->flush();

        $this->setReference('website', $website);
    }
}
