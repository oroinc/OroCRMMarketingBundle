<?php

namespace Oro\Bundle\CampaignBundle\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Async\Topic\SendEmailCampaignTopic;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;

/**
 * Perform email campaign sending.
 */
class EmailCampaignSendProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    private LoggerInterface $logger;

    private ManagerRegistry $registry;

    private EmailCampaignSenderBuilder $senderBuilder;

    private JobRunner $jobRunner;

    public function __construct(
        LoggerInterface $logger,
        ManagerRegistry $registry,
        EmailCampaignSenderBuilder $senderBuilder,
        JobRunner $jobRunner
    ) {
        $this->logger = $logger;
        $this->registry = $registry;
        $this->senderBuilder = $senderBuilder;
        $this->jobRunner = $jobRunner;
    }

    /**
     * {@inheritDoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();
        $emailCampaign = $this->getEmailCampaign($messageBody);

        if (!$emailCampaign) {
            return self::REJECT;
        }

        $result = $this->jobRunner->runUniqueByMessage(
            $message,
            function () use ($emailCampaign) {
                $sender = $this->senderBuilder->getSender($emailCampaign);
                $sender->send();

                return true;
            }
        );

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [SendEmailCampaignTopic::getName()];
    }

    private function getEmailCampaign(array $body): ?EmailCampaign
    {
        $emailCampaignId = $body['email_campaign'];

        $emailCampaign = $this->registry
            ->getManagerForClass(EmailCampaign::class)
            ?->find(EmailCampaign::class, $emailCampaignId);

        if (!$emailCampaign) {
            $this->logger->notice(sprintf('Email campaign with id %d was not found', $emailCampaignId));
        }

        return $emailCampaign;
    }
}
