<?php

namespace Oro\Bundle\CampaignBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;

class AddCampaignCodeHistory extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Campaign[] $campaigns */
        $campaigns = $manager->getRepository('OroCampaignBundle:Campaign')->findAll();
        foreach ($campaigns as $campaign) {
            $codeHistory = new CampaignCodeHistory();
            $codeHistory->setCampaign($campaign);
            $codeHistory->setCode($campaign->getCode());

            $manager->persist($codeHistory);
        }

        $manager->flush();
    }
}
