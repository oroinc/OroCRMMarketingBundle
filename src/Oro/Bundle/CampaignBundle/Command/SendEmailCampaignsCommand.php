<?php
declare(strict_types=1);

namespace Oro\Bundle\CampaignBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\CronBundle\Command\CronCommandActivationInterface;
use Oro\Bundle\CronBundle\Command\CronCommandScheduleDefinitionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends scheduled email campaigns.
 */
class SendEmailCampaignsCommand extends Command implements
    CronCommandScheduleDefinitionInterface,
    CronCommandActivationInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:send-email-campaigns';

    private ManagerRegistry $doctrine;
    private EmailCampaignSenderBuilder $emailCampaignSenderBuilder;

    public function __construct(
        ManagerRegistry $doctrine,
        EmailCampaignSenderBuilder $emailCampaignSenderBuilder
    ) {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->emailCampaignSenderBuilder = $emailCampaignSenderBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultDefinition(): string
    {
        return '*/1 * * * *';
    }

    /**
     * {@inheritDoc}
     */
    public function isActive(): bool
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
        $emailCampaigns = $this->getEmailCampaignRepository()->findEmailCampaignsToSend();
        if (!$emailCampaigns) {
            $output->writeln('<info>No email campaigns to send</info>');

            return 0;
        }

        $output->writeln(sprintf('<comment>Email campaigns to send:</comment> %d', count($emailCampaigns)));

        $this->send($output, $emailCampaigns);
        $output->writeln('<info>Finished email campaigns sending</info>');

        return 0;
    }

    private function send(OutputInterface $output, array $emailCampaigns): void
    {
        /** @var EmailCampaign $emailCampaign */
        foreach ($emailCampaigns as $emailCampaign) {
            $output->writeln(sprintf('<info>Sending email campaign</info>: %s', $emailCampaign->getName()));

            $sender = $this->emailCampaignSenderBuilder->getSender($emailCampaign);
            $sender->send();
        }
    }

    private function getEmailCampaignRepository(): EmailCampaignRepository
    {
        return $this->doctrine->getRepository(EmailCampaign::class);
    }
}
