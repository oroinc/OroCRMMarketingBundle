<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandActivationInterface;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Oro\Bundle\TrackingBundle\Processor\TrackingProcessor;
use Oro\Component\Log\OutputLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Parses tracking logs.
 */
class TrackCommand extends Command implements
    CronCommandScheduleDefinitionInterface,
    CronCommandActivationInterface
{
    public const STATUS_SUCCESS = 0;

    /** @var string */
    protected static $defaultName = 'oro:cron:tracking:parse';

    private TrackingProcessor $trackingProcessor;

    public function __construct(TrackingProcessor $trackingProcessor)
    {
        parent::__construct();
        $this->trackingProcessor = $trackingProcessor;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDefinition(): string
    {
        return '*/5 * * * *';
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
    {
        return $this->trackingProcessor->hasTrackingEventsToProcess();
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this
            ->addOption(
                'max-execution-time',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Max execution time in minutes (use 0 for unlimited)',
                5
            )
            ->setDescription('Parses tracking logs.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command parses tracking logs.

  <info>php %command.full_name%</info>

The <info>--max-execution-time</info> option can be used to prevent parallel processing
as this command is run by cron every 5 minutes, which is also the default value
for the maximum execution time (use 0 to remove the limit):

  <info>php %command.full_name% --max-execution-time=<minutes></info>
  <info>php %command.full_name% --max-execution-time=0</info>

HELP
            )
            ->addUsage('--max-execution-time=<minutes>')
            ->addUsage('--max-execution-time=0')
        ;
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new OutputLogger($output);

        $maxExecutionTime = $input->getOption('max-execution-time');
        if ($maxExecutionTime && is_numeric($maxExecutionTime)) {
            $this->trackingProcessor->setMaxExecutionTime($maxExecutionTime);
        }

        $this->trackingProcessor->setLogger($logger);
        $this->trackingProcessor->process();

        return self::STATUS_SUCCESS;
    }
}
