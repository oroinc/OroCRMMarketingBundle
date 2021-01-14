<?php
declare(strict_types=1);

namespace Oro\Bundle\CampaignBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends scheduled email campaigns.
 */
class SendEmailCampaignsCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:send-email-campaigns';

    private ManagerRegistry $registry;
    private FeatureChecker $featureChecker;
    private EmailCampaignSenderBuilder $emailCampaignSenderBuilder;

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

    public function getDefaultDefinition()
    {
        return '*/1 * * * *';
    }

    public function isActive()
    {
        $count = $this->getEmailCampaignRepository()->countEmailCampaignsToSend();

        return ($count > 0);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function configure()
    {
        $this
            ->setDescription('Sends scheduled email campaigns.')
            ->setHelp(
                <<<'HELP'
The <info>%command.name%</info> command sends scheduled email campaigns.

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
    protected function send(OutputInterface $output, array $emailCampaigns): void
    {
        foreach ($emailCampaigns as $emailCampaign) {
            $output->writeln(sprintf('<info>Sending email campaign</info>: %s', $emailCampaign->getName()));

            $sender = $this->emailCampaignSenderBuilder->getSender($emailCampaign);
            $sender->send();
        }
    }

    protected function getEmailCampaignRepository(): EmailCampaignRepository
    {
        return $this->registry->getRepository('OroCampaignBundle:EmailCampaign');
    }
}
