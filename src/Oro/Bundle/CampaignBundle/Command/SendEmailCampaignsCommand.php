<?php

namespace Oro\Bundle\CampaignBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to send scheduled email campaigns
 */
class SendEmailCampaignsCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:send-email-campaigns';

    /** @var ManagerRegistry */
    private $registry;

    /** @var FeatureChecker */
    private $featureChecker;

    /** @var EmailCampaignSenderBuilder */
    private $emailCampaignSenderBuilder;

    /**
     * @param ManagerRegistry $registry
     * @param FeatureChecker $featureChecker
     * @param EmailCampaignSenderBuilder $emailCampaignSenderBuilder
     */
    public function __construct(
        ManagerRegistry $registry,
        FeatureChecker $featureChecker,
        EmailCampaignSenderBuilder $emailCampaignSenderBuilder
    ) {
        parent::__construct();

        $this->registry = $registry;
        $this->featureChecker = $featureChecker;
        $this->emailCampaignSenderBuilder = $emailCampaignSenderBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        $count = $this->getEmailCampaignRepository()->countEmailCampaignsToSend();

        return ($count > 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Send email campaigns');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->featureChecker->isFeatureEnabled('campaign')) {
            $output->writeln('The campaign feature is disabled. The command will not run.');

            return 0;
        }

        $emailCampaigns = $this->getEmailCampaignRepository()->findEmailCampaignsToSend();

        if (!$emailCampaigns) {
            $output->writeln('<info>No email campaigns to send</info>');
            return 0;
        }

        $output->writeln(
            sprintf('<comment>Email campaigns to send:</comment> %d', count($emailCampaigns))
        );

        $this->send($output, $emailCampaigns);
        $output->writeln(sprintf('<info>Finished email campaigns sending</info>'));

        return 0;
    }

    /**
     * Send email campaigns
     *
     * @param OutputInterface $output
     * @param EmailCampaign[] $emailCampaigns
     */
    protected function send($output, array $emailCampaigns)
    {
        foreach ($emailCampaigns as $emailCampaign) {
            $output->writeln(sprintf('<info>Sending email campaign</info>: %s', $emailCampaign->getName()));

            $sender = $this->emailCampaignSenderBuilder->getSender($emailCampaign);
            $sender->send();
        }
    }

    /**
     * @return EmailCampaignRepository
     */
    protected function getEmailCampaignRepository()
    {
        return $this->registry->getRepository('OroCampaignBundle:EmailCampaign');
    }
}
