<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;

/**
 * Loads campaign code history for existing campaigns.
 */
class AddCampaignCodeHistory extends AbstractFixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /** @var Campaign[] $campaigns */
        $campaigns = $manager->getRepository(Campaign::class)->findAll();
        foreach ($campaigns as $campaign) {
            $codeHistory = new CampaignCodeHistory();
            $codeHistory->setCampaign($campaign);
            $codeHistory->setCode($campaign->getCode());

            $manager->persist($codeHistory);
        }

        $manager->flush();
    }
}
