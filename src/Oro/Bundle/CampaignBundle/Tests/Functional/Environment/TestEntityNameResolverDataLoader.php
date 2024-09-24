<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Environment;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;

    public function __construct(TestEntityNameResolverDataLoaderInterface $innerDataLoader)
    {
        $this->innerDataLoader = $innerDataLoader;
    }

    #[\Override]
    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (Campaign::class === $entityClass) {
            $campaign = new Campaign();
            $campaign->setOrganization($repository->getReference('organization'));
            $campaign->setOwner($repository->getReference('user'));
            $campaign->setReportPeriod(Campaign::PERIOD_MONTHLY);
            $campaign->setCode('cmp');
            $campaign->setName('Test Campaign');
            $repository->setReference('campaign', $campaign);
            $em->persist($campaign);
            $em->flush();

            return ['campaign'];
        }

        if (EmailCampaign::class === $entityClass) {
            $emailCampaign = new EmailCampaign();
            $emailCampaign->setOrganization($repository->getReference('organization'));
            $emailCampaign->setOwner($repository->getReference('user'));
            $emailCampaign->setSchedule(EmailCampaign::SCHEDULE_MANUAL);
            $emailCampaign->setTransport('test');
            $emailCampaign->setName('Test Email Campaign');
            $repository->setReference('emailCampaign', $emailCampaign);
            $em->persist($emailCampaign);
            $em->flush();

            return ['emailCampaign'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    #[\Override]
    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (Campaign::class === $entityClass) {
            return 'Test Campaign';
        }
        if (EmailCampaign::class === $entityClass) {
            return 'Test Email Campaign';
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }
}
