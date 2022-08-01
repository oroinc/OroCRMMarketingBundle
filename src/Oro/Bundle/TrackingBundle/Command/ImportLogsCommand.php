<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Command;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CronBundle\Command\CronCommandActivationInterface;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\TrackingBundle\Entity\TrackingData;
use Oro\Bundle\TrackingBundle\Tools\TrackingDataFolderSelector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Imports event tracking logs.
 */
class ImportLogsCommand extends Command implements
    CronCommandScheduleDefinitionInterface,
    CronCommandActivationInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:import-tracking';

    private DoctrineJobRepository $doctrineJobRepository;
    private JobExecutor $jobExecutor;
    private ConfigManager $configManager;
    private TrackingDataFolderSelector $trackingDataFolderSelector;

    public function __construct(
        DoctrineJobRepository $doctrineJobRepository,
        JobExecutor $jobExecutor,
        ConfigManager $configManager,
        TrackingDataFolderSelector $trackingDataFolderSelector
    ) {
        $this->doctrineJobRepository = $doctrineJobRepository;
        $this->jobExecutor = $jobExecutor;
        $this->configManager = $configManager;
        $this->trackingDataFolderSelector = $trackingDataFolderSelector;
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDefinition(): string
    {
        return '1 * * * *';
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
    {
        $fs = new Filesystem();
        $finder = new Finder();
        $directory = $this->getDirectory();

        if (!$fs->exists($directory)) {
            return false;
        }

        $finder
            ->files()
            ->notName($this->getIgnoredFilename())
            ->notName('settings.ser')
            ->in($directory);

        if (!$finder->count()) {
            return false;
        }

        return true;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this
            ->addOption('directory', 'd', InputOption::VALUE_OPTIONAL, 'Logs directory')
            ->setDescription('Imports event tracking logs.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command imports event tracking logs.

  <info>php %command.full_name%</info>

The <info>--directory</info> option can be used to provide a different path to a directory
that contains even tracking log files:

  <info>php %command.full_name% --directory=<path></info>

HELP
            )
            ->addUsage('--directory=<path>')
        ;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fs     = new Filesystem();
        $finder = new Finder();

        if (!$directory = $input->getOption('directory')) {
            $directory = $this->getDirectory();
        }

        if (!$fs->exists($directory)) {
            $fs->mkdir($directory);

            $output->writeln('<info>Logs not found</info>');

            return 0;
        }

        $finder
            ->files()
            ->notName($this->getIgnoredFilename())
            ->notName('settings.ser')
            ->in($directory);

        if (!$finder->count()) {
            $output->writeln('<info>Logs not found</info>');

            return 0;
        }

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $pathName = $file->getPathname();
            $fileName = $file->getFilename();

            $options = [
                ProcessorRegistry::TYPE_IMPORT => [
                    'entityName' => TrackingData::class,
                    'processorAlias' => 'oro_tracking.processor.data',
                    'file' => $pathName,
                ],
            ];

            if ($this->isFileProcessed($options)) {
                $output->writeln(sprintf('<info>"%s" already processed</info>', $fileName));

                continue;
            }

            $jobResult = $this->jobExecutor->executeJob(
                ProcessorRegistry::TYPE_IMPORT,
                'import_log_to_database',
                $options
            );

            if ($jobResult->isSuccessful()) {
                $output->writeln(
                    sprintf('<info>Successful</info>: "%s"', $fileName)
                );
                $fs->remove($pathName);
            } else {
                foreach ($jobResult->getFailureExceptions() as $exception) {
                    $output->writeln(
                        sprintf(
                            '<error>Error</error>: "%s".',
                            $exception
                        )
                    );
                }
            }
        }

        return 0;
    }

    protected function getDirectory(): string
    {
        return $this->trackingDataFolderSelector->retrieve();
    }

    protected function isFileProcessed(array $options): bool
    {
        $manager = $this->doctrineJobRepository->getJobManager();

        $qb = $manager
            ->getRepository(JobExecution::class)
            ->createQueryBuilder('je');

        /** @var QueryBuilder $qb */
        $result = $qb
            ->select('COUNT(je) as jobs')
            ->leftJoin('je.jobInstance', 'ji')
            ->where($qb->expr()->lt('je.status', ':status'))
            ->setParameter('status', BatchStatus::FAILED)
            ->andWhere('ji.rawConfiguration = :rawConfiguration')
            ->setParameter(
                'rawConfiguration',
                $manager->getConnection()->convertToDatabaseValue($options, Types::ARRAY)
            )
            ->getQuery()
            ->getOneOrNullResult();

        return $result['jobs'] > 0;
    }

    protected function getIgnoredFilename(): string
    {
        $logRotateInterval = $this->configManager->get('oro_tracking.log_rotate_interval');

        $rotateInterval = 60;
        $currentPart    = 1;
        if ($logRotateInterval > 0 && $logRotateInterval < 60) {
            $rotateInterval = (int)$logRotateInterval;
            $passingMinute  = (int)(date('i')) + 1;
            $currentPart    = ceil($passingMinute / $rotateInterval);
        }

        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        return $date->format('Ymd-H') . '-' . $rotateInterval . '-' . $currentPart . '.log';
    }
}
