<?php

namespace Oro\Bundle\TrackingBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\TrackingBundle\Processor\TrackingProcessor;
use Oro\Component\Log\OutputLogger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TrackCommand extends ContainerAwareCommand implements CronCommandInterface
{
    const STATUS_SUCCESS = 0;
    const COMMAND_NAME   = 'oro:cron:tracking:parse';

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/5 * * * *';
    }

    public function isActive()
    {
        $featureChecker = $this->getContainer()->get('oro_featuretoggle.checker.feature_checker');
        if (!$featureChecker->isFeatureEnabled('tracking')) {
            return false;
        }
        /** @var TrackingProcessor $processor */
        $processor = $this->getContainer()->get('oro_tracking.processor.tracking_processor');

        return  $processor->hasEntitiesToProcess();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::COMMAND_NAME)
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
        $featureChecker = $this->getContainer()->get('oro_featuretoggle.checker.feature_checker');
        if (!$featureChecker->isFeatureEnabled('tracking')) {
            $output->writeln('The tracking feature is disabled. The command will not run.');

            return 0;
        }

        $logger = new OutputLogger($output);

        /** @var TrackingProcessor $processor */
        $processor = $this->getContainer()->get('oro_tracking.processor.tracking_processor');

        $maxExecutionTime = $input->getOption('max-execution-time');
        if ($maxExecutionTime && is_numeric($maxExecutionTime)) {
            $processor->setMaxExecutionTime($maxExecutionTime);
        }

        $processor->setLogger($logger);
        $processor->process();

        return self::STATUS_SUCCESS;
    }
}
