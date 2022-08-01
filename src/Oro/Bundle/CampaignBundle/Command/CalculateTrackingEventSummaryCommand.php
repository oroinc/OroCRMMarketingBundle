<?php
declare(strict_types=1);

namespace Oro\Bundle\CampaignBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Entity\Repository\TrackingEventSummaryRepository;
use Oro\Bundle\CampaignBundle\Entity\TrackingEventSummary;
use Oro\Bundle\CronBundle\Command\CronCommandActivationInterface;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Calculates tracking event summary (campaign statistics).
 */
class CalculateTrackingEventSummaryCommand extends Command implements
    CronCommandScheduleDefinitionInterface,
    CronCommandActivationInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:calculate-tracking-event-summary';

    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDefinition(): string
    {
        // 00:01 every day
        return '1 0 * * *';
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
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
        $campaigns = $this->getCampaignRepository()->findAll();
        if (!$campaigns) {
            $output->writeln('<info>No campaigns found</info>');

            return 0;
        }

        $output->writeln(sprintf('<comment>Campaigns to calculate:</comment> %d', count($campaigns)));

        $this->calculate($output, $campaigns);
        $output->writeln('<info>Finished campaigns statistic calculation</info>');

        return 0;
    }

    private function calculate(OutputInterface $output, array $campaigns): void
    {
        $em = $this->getEntityManager(Campaign::class);
        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign) {
            $output->writeln(sprintf('<info>Calculating statistic for campaign</info>: %s', $campaign->getName()));

            $this->calculateForCampaign($campaign);

            $refreshDate = new \DateTime('-1 day', new \DateTimeZone('UTC'));
            $campaign->setReportRefreshDate($refreshDate);
            $em->persist($campaign);
        }

        $em->flush();
    }

    private function calculateForCampaign(Campaign $campaign): void
    {
        $em = $this->getEntityManager(TrackingEventSummary::class);
        $events = $this->getTrackingEventSummaryRepository()->getSummarizedStatistic($campaign);
        foreach ($events as $event) {
            $summary = new TrackingEventSummary();
            $summary->setCode($event['code']);
            $summary->setWebsite($em->getReference(TrackingWebsite::class, $event['websiteId']));
            $summary->setName($event['name']);
            $summary->setVisitCount($event['visitCount']);
            $summary->setLoggedAt(new \DateTime($event['loggedAtDate'], new \DateTimeZone('UTC')));

            $em->persist($summary);
        }

        $em->flush();
    }

    private function getEntityManager(string $class): EntityManagerInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doctrine->getManagerForClass($class);
    }

    private function getCampaignRepository(): CampaignRepository
    {
        return $this->doctrine->getRepository(Campaign::class);
    }

    private function getTrackingEventSummaryRepository(): TrackingEventSummaryRepository
    {
        return $this->doctrine->getRepository(TrackingEventSummary::class);
    }
}
