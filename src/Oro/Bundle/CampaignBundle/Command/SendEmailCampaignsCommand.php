<?php

namespace Oro\Bundle\CampaignBundle\Command;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to send scheduled email campaigns
 */
class SendEmailCampaignsCommand extends ContainerAwareCommand implements CronCommandInterface
{
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
            ->setName('oro:cron:send-email-campaigns')
            ->setDescription('Send email campaigns');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $featureChecker = $this->getContainer()->get('oro_featuretoggle.checker.feature_checker');
        if (!$featureChecker->isFeatureEnabled('campaign')) {
            $output->writeln('The campaign feature is disabled. The command will not run.');

            return 0;
        }

        $emailCampaigns = $this->getEmailCampaignRepository()->findEmailCampaignsToSend();

        if (!$emailCampaigns) {
            $output->writeln('<info>No email campaigns to send</info>');
            return;
        }

        $output->writeln(
            sprintf('<comment>Email campaigns to send:</comment> %d', count($emailCampaigns))
        );

        $this->send($output, $emailCampaigns);
        $output->writeln(sprintf('<info>Finished email campaigns sending</info>'));
    }

    /**
     * Send email campaigns
     *
     * @param OutputInterface $output
     * @param EmailCampaign[] $emailCampaigns
     */
    protected function send($output, array $emailCampaigns)
    {
        $senderFactory = $this->getSenderFactory();

        foreach ($emailCampaigns as $emailCampaign) {
            $output->writeln(sprintf('<info>Sending email campaign</info>: %s', $emailCampaign->getName()));

            $sender = $senderFactory->getSender($emailCampaign);
            $sender->send($emailCampaign);
        }
    }

    /**
     * @return EmailCampaignSenderBuilder
     */
    protected function getSenderFactory()
    {
        return $this->getContainer()->get('oro_campaign.email_campaign.sender.builder');
    }

    /**
     * @return EmailCampaignRepository
     */
    protected function getEmailCampaignRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository('OroCampaignBundle:EmailCampaign');
    }
}
