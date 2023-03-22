<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Async\EmailCampaignSendProcessor;
use Oro\Bundle\CampaignBundle\Async\Topic\SendEmailCampaignTopic;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSender;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\Testing\ReflectionUtil;
use Psr\Log\LoggerInterface;

class EmailCampaignSendProcessorTest extends \PHPUnit\Framework\TestCase
{
    private LoggerInterface|\PHPUnit\Framework\MockObject\MockObject $logger;

    private ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $registry;

    private EmailCampaignSenderBuilder|\PHPUnit\Framework\MockObject\MockObject $senderBuilder;

    private JobRunner|\PHPUnit\Framework\MockObject\MockObject $jobRunner;

    private EmailCampaignSendProcessor $processor;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->senderBuilder = $this->createMock(EmailCampaignSenderBuilder::class);
        $this->jobRunner = $this->createMock(JobRunner::class);

        $this->processor = new EmailCampaignSendProcessor(
            $this->logger,
            $this->registry,
            $this->senderBuilder,
            $this->jobRunner
        );
    }

    public function testFetSubscribedTopics(): void
    {
        self::assertEquals([SendEmailCampaignTopic::getName()], EmailCampaignSendProcessor::getSubscribedTopics());
    }

    public function testProcessNoEmailCampaign(): void
    {
        $emailCampaignId = 1;
        $emailCampaign = null;
        $this->assertEmailCampaignFind($emailCampaign);

        $session = $this->createMock(SessionInterface::class);
        $message = $this->createMock(MessageInterface::class);
        $message->expects(self::any())
            ->method('getBody')
            ->willReturn(['email_campaign' => $emailCampaignId]);

        $this->logger->expects(self::once())
            ->method('notice')
            ->with('Email campaign with id 1 was not found');

        self::assertEquals(MessageProcessorInterface::REJECT, $this->processor->process($message, $session));
    }

    public function testProcess(): void
    {
        $emailCampaignId = 1;
        $emailCampaign = new EmailCampaign();
        ReflectionUtil::setId($emailCampaign, $emailCampaignId);
        $this->assertEmailCampaignFind($emailCampaign);
        $jobName = SendEmailCampaignTopic::getName() . ':' . $emailCampaignId;

        $session = $this->createMock(SessionInterface::class);
        $message = $this->createMock(MessageInterface::class);
        $message->expects(self::any())
            ->method('getBody')
            ->willReturn(['email_campaign' => $emailCampaignId]);
        $message->expects(self::any())
            ->method('getMessageId')
            ->willReturn('MID');
        $message->method('getProperty')
            ->with(JobAwareTopicInterface::UNIQUE_JOB_NAME)
            ->willReturn($jobName);

        $this->logger->expects(self::never())
            ->method($this->anything());

        $sender = $this->createMock(EmailCampaignSender::class);
        $sender->expects(self::once())
            ->method('send');

        $this->senderBuilder->expects(self::once())
            ->method('getSender')
            ->with($emailCampaign)
            ->willReturn($sender);

        $job = $this->createMock(Job::class);
        $this->jobRunner->expects(self::once())
            ->method('runUniqueByMessage')
            ->with($message)
            ->willReturnCallback(function ($message, $closure) use ($job, $jobName) {
                self::assertEquals('MID', $message->getMessageId());
                self::assertEquals($jobName, $message->getProperty(JobAwareTopicInterface::UNIQUE_JOB_NAME));

                return $closure($this->jobRunner, $job);
            });

        self::assertEquals(MessageProcessorInterface::ACK, $this->processor->process($message, $session));
    }

    private function assertEmailCampaignFind(EmailCampaign $emailCampaign = null): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())
            ->method('find')
            ->with(EmailCampaign::class, 1)
            ->willReturn($emailCampaign);

        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(EmailCampaign::class)
            ->willReturn($em);
    }
}
