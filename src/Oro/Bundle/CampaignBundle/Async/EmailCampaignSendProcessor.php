<?php

namespace Oro\Bundle\CampaignBundle\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;

/**
 * Perform email campaign sending.
 */
class EmailCampaignSendProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var EmailCampaignSenderBuilder
     */
    private $senderBuilder;

    /**
     * @var JobRunner
     */
    private $jobRunner;

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
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());
        $emailCampaign = $this->getEmailCampaign($body);

        if (!$emailCampaign) {
            return self::REJECT;
        }

        $result = $this->jobRunner->runUnique(
            $message->getMessageId(),
            Topics::SEND_EMAIL_CAMPAIGN . ':' . $emailCampaign->getId(),
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
    public static function getSubscribedTopics()
    {
        return [Topics::SEND_EMAIL_CAMPAIGN];
    }

    private function getEmailCampaign(array $body): ?EmailCampaign
    {
        $emailCampaignId = $body['email_campaign'];

        $emailCampaign = $this->registry
            ->getManagerForClass(EmailCampaign::class)
            ->find(EmailCampaign::class, $emailCampaignId);

        if (!$emailCampaign) {
            $this->logger->notice(sprintf('Email campaign with id %d was not found', $emailCampaignId));
        }

        return $emailCampaign;
    }
}
