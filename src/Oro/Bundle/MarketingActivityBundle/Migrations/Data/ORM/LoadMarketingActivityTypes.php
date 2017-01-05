<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivityType;

class LoadMarketingActivityTypes extends AbstractFixture
{
    /**
     * Load available marketing activity types
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $types = [
            MarketingActivityType::TYPE_SEND,
            MarketingActivityType::TYPE_OPEN,
            MarketingActivityType::TYPE_CLICK,
            MarketingActivityType::TYPE_SOFT_BOUNCE,
            MarketingActivityType::TYPE_HARD_BOUNCE,
        ];

        foreach ($types as $typeCode) {
            $type = new MarketingActivityType();
            $type->setName($typeCode);
            $type->setLabel('oro.marketingactivity.type.' . $typeCode);

            $manager->persist($type);
        }

        $manager->flush();
    }
}
