<?php
declare(strict_types=1);

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
 * Calculates tracking event summary (campaign statistics).
 */
class CalculateTrackingEventSummaryCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:calculate-tracking-event-summary';

    private FeatureChecker $featureChecker;
    private DoctrineHelper $doctrineHelper;
    private ?TrackingEventSummaryRepository $trackingEventRepository = null;

    public function __construct(FeatureChecker $featureChecker, DoctrineHelper $doctrineHelper)
    {
        parent::__construct();

        $this->featureChecker = $featureChecker;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function getDefaultDefinition()
    {
        // 00:01 every day
        return '1 0 * * *';
    }

    public function isActive()
    {
        $count = $this->getCampaignRepository()->getCount();

        return ($count > 0);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this->setDescription('Calculates tracking event summary (campaign statistics).')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command calculates tracking event summary (campaign statistics).

  <info>php %command.full_name%</info>

HELP
            )
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpMissingParentCallCommonInspection
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
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function calculate(OutputInterface $output, array $campaigns): void
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

    protected function getCampaignRepository(): CampaignRepository
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doctrineHelper->getEntityRepository(Campaign::class);
    }

    protected function getTrackingEventSummaryRepository(): TrackingEventSummaryRepository
    {
        if (!$this->trackingEventRepository) {
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->trackingEventRepository = $this->doctrineHelper->getEntityRepository(TrackingEventSummary::class);
        }

        return $this->trackingEventRepository;
    }
}
