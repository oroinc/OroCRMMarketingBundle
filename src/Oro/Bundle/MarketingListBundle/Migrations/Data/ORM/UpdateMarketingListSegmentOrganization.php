<?php

namespace Oro\Bundle\MarketingListBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * Updates organization for marketing list segments.
 */
class UpdateMarketingListSegmentOrganization extends AbstractFixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $entitiesToFlush = [];
        $marketingLists = $manager->getRepository(MarketingList::class)->findAll();
        foreach ($marketingLists as $marketingList) {
            $segment = $marketingList->getSegment();
            if (!$segment->getOrganization()) {
                $segment->setOrganization($marketingList->getOrganization());
                $entitiesToFlush[] = $segment;
            }
        }
        $manager->flush($entitiesToFlush);
    }
}
