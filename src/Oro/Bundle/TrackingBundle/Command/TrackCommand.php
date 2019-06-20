<?php

namespace Oro\Bundle\TrackingBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\TrackingBundle\Processor\TrackingProcessor;
use Oro\Component\Log\OutputLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Parse tracking logs
 */
class TrackCommand extends Command implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;

    /** @var string */
    protected static $defaultName = 'oro:cron:tracking:parse';

    /** @var FeatureChecker */
    private $featureChecker;

    /** @var TrackingProcessor */
    private $trackingProcessor;

    /**
     * @param FeatureChecker $featureChecker
     * @param TrackingProcessor $trackingProcessor
     */
    public function __construct(FeatureChecker $featureChecker, TrackingProcessor $trackingProcessor)
    {
        parent::__construct();

        $this->featureChecker = $featureChecker;
        $this->trackingProcessor = $trackingProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        if (!$this->featureChecker->isFeatureEnabled('tracking')) {
            return false;
        }

        return $this->trackingProcessor->hasEntitiesToProcess();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Parse tracking logs')
            ->addOption(
                'max-execution-time',
                'm',
                InputOption::VALUE_OPTIONAL,
                'Max execution time (in minutes). "0" means - unlimited. <comment>(default: 5)</comment>'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->featureChecker->isFeatureEnabled('tracking')) {
            $output->writeln('The tracking feature is disabled. The command will not run.');

            return 0;
        }

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
