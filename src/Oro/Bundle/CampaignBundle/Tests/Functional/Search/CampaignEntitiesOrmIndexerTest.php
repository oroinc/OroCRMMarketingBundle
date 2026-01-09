<?php

declare(strict_types=1);

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Search;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SearchBundle\Tests\Functional\Engine\AbstractEntitiesOrmIndexerTest;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Tests that all marketing package entities can be indexed without type casting errors with the ORM search engine.
 * This test covers entities from CampaignBundle and MarketingListBundle.
 *
 * @group search
 * @dbIsolationPerTest
 */
class CampaignEntitiesOrmIndexerTest extends AbstractEntitiesOrmIndexerTest
{
    #[\Override]
    protected function getSearchableEntityClassesToTest(): array
    {
        return [
            Campaign::class,
            EmailCampaign::class,
            MarketingList::class
        ];
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOrganization::class, LoadUser::class]);

        $manager = $this->getDoctrine()->getManagerForClass(Campaign::class);
        /** @var Organization $organization */
        $organization = $this->getReference(LoadOrganization::ORGANIZATION);
        /** @var User $owner */
        $owner = $this->getReference(LoadUser::USER);

        $marketingListType = $manager->getRepository(MarketingListType::class)
            ->findOneBy(['name' => MarketingListType::TYPE_DYNAMIC]);

        $marketingList = (new MarketingList())
            ->setName('Test Marketing List')
            ->setDescription('Test Marketing List Description')
            ->setOrganization($organization)
            ->setOwner($owner)
            ->setEntity('Oro\Bundle\ContactBundle\Entity\Contact')
            ->setType($marketingListType);
        $marketingList->setDefinition('{"columns":[]}');
        $this->persistTestEntity($marketingList);

        $campaign = new Campaign();
        $campaign->setName('Test Campaign');
        $campaign->setCode('test_campaign');
        $campaign->setDescription('Test Campaign Description');
        $campaign->setOrganization($organization)->setReportPeriod(Campaign::PERIOD_MONTHLY);
        $this->persistTestEntity($campaign);

        $emailCampaign = (new EmailCampaign())
            ->setName('Test Email Campaign')
            ->setDescription('Test Email Campaign Description')
            ->setOrganization($organization)
            ->setCampaign($campaign)
            ->setMarketingList($marketingList)
            ->setSenderEmail('test@example.com')
            ->setSenderName('Test Sender')
            ->setSchedule(EmailCampaign::SCHEDULE_MANUAL)
            ->setTransport('internal');
        $this->persistTestEntity($emailCampaign);

        $manager->flush();
    }
}
