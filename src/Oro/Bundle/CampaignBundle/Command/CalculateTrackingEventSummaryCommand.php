<?php

namespace Oro\Bundle\CampaignBundle\Command;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Entity\Repository\TrackingEventSummaryRepository;
use Oro\Bundle\CampaignBundle\Entity\TrackingEventSummary;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Calculate Tracking Event Summary
 */
class CalculateTrackingEventSummaryCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:calculate-tracking-event-summary';

    /** @var FeatureChecker */
    private $featureChecker;

    /** @var DoctrineHelper */
    private $doctrineHelper;

    /** @var TrackingEventSummaryRepository */
    private $trackingEventRepository;

    /**
     * @param FeatureChecker $featureChecker
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(FeatureChecker $featureChecker, DoctrineHelper $doctrineHelper)
    {
        parent::__construct();

        $this->featureChecker = $featureChecker;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Run command at 00:01 every day.
     *
     * @return string
     */
    public function getDefaultDefinition()
    {
        return '1 0 * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $count = $this->getCampaignRepository()->getCount();

        return ($count > 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Calculate Tracking Event Summary');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->featureChecker->isFeatureEnabled('tracking') ||
            !$this->featureChecker->isFeatureEnabled('campaign')
        ) {
            $output->writeln('This feature is disabled. The command will not run.');

            return 0;
        }

        $campaigns = $this->getCampaignRepository()->findAll();

        if (!$campaigns) {
            $output->writeln('<info>No campaigns found</info>');

            return 0;
        }

        $output->writeln(
            sprintf('<comment>Campaigns to calculate:</comment> %d', count($campaigns))
        );

        $this->calculate($output, $campaigns);
        $output->writeln(sprintf('<info>Finished campaigns statistic calculation</info>'));

        return 0;
    }

    /**
     * Calculate tracking event statistic for campaigns
     *
     * @param OutputInterface $output
     * @param Campaign[] $campaigns
     */
    protected function calculate($output, array $campaigns)
    {
        $em = $this->doctrineHelper->getEntityManagerForClass(Campaign::class);
        foreach ($campaigns as $campaign) {
            $output->writeln(sprintf('<info>Calculating statistic for campaign</info>: %s', $campaign->getName()));

            $this->calculateForCampaign($campaign);

            $refreshDate = new \DateTime('-1 day', new \DateTimeZone('UTC'));
            $campaign->setReportRefreshDate($refreshDate);
            $em->persist($campaign);
        }

        $em->flush();
    }

    /**
     * @param Campaign $campaign
     */
    protected function calculateForCampaign(Campaign $campaign)
    {
        $trackingEventRepository = $this->getTrackingEventSummaryRepository();
        $events = $trackingEventRepository->getSummarizedStatistic($campaign);

        $em = $this->doctrineHelper->getEntityManagerForClass(TrackingEventSummary::class);
        foreach ($events as $event) {
            /** @var TrackingWebsite $website */
            $website = $this->doctrineHelper->getEntityReference(
                TrackingWebsite::class,
                $event['websiteId']
            );

            $summary = new TrackingEventSummary();
            $summary->setCode($event['code']);
            $summary->setWebsite($website);
            $summary->setName($event['name']);
            $summary->setVisitCount($event['visitCount']);
            $summary->setLoggedAt(new \DateTime($event['loggedAtDate'], new \DateTimeZone('UTC')));

            $em->persist($summary);
        }

        $em->flush();
    }

    /**
     * @return CampaignRepository
     */
    protected function getCampaignRepository()
    {
        return $this->doctrineHelper->getEntityRepository(Campaign::class);
    }

    /**
     * @return TrackingEventSummaryRepository
     */
    protected function getTrackingEventSummaryRepository()
    {
        if (!$this->trackingEventRepository) {
            $this->trackingEventRepository = $this->doctrineHelper->getEntityRepository(TrackingEventSummary::class);
        }

        return $this->trackingEventRepository;
    }
}
