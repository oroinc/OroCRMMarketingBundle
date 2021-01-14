<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;

class UpdateMarketingListSegmentOrganization extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $marketingLists = $manager->getRepository('OroMarketingListBundle:MarketingList')->findAll();
        $entitiesToFlush = [];

        foreach ($marketingLists as $marketingList) {
            $segment = $marketingList->getSegment();
            if (!$segment->getOrganization()) {
                $segment->setOrganization($marketingList->getOrganization());
                $entitiesToFlush[] = $segment;
            }
        }

        /** @var EntityManager $manager */
        $manager->flush($entitiesToFlush);
    }
}
