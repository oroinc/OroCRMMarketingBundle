<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Transport\EmailTransport;

class LoadEmailCampaignData extends AbstractFixture
{
    private static $data = [
        [
            'name' => 'CampaignBundle.Campaign1',
            'schedule' => EmailCampaign::SCHEDULE_MANUAL,
            'transport' => EmailTransport::NAME,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::$data as $data) {
            $emailCampaign = new EmailCampaign();
            $emailCampaign->setName($data['name']);
            $emailCampaign->setSchedule($data['schedule']);
            $emailCampaign->setTransport($data['transport']);

            $manager->persist($emailCampaign);
            $this->addReference($data['name'], $emailCampaign);
        }

        $manager->flush();
    }
}
