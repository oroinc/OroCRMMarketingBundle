<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Async\EmailCampaignSendProcessor;
use Oro\Bundle\CampaignBundle\Async\Topics;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSender;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\Testing\Unit\EntityTrait;
use Psr\Log\LoggerInterface;

class EmailCampaignSendProcessorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registry;

    /**
     * @var EmailCampaignSenderBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $senderBuilder;

    /**
     * @var JobRunner|\PHPUnit\Framework\MockObject\MockObject
     */
    private $jobRunner;

    /**
     * @var EmailCampaignSendProcessor
     */
    private $processor;

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

    public function testFetSubscribedTopics()
    {
        $this->assertEquals([Topics::SEND_EMAIL_CAMPAIGN], EmailCampaignSendProcessor::getSubscribedTopics());
    }

    public function testProcessNoEmailCampaign()
    {
        $emailCampaignId = 1;
        $emailCampaign = null;
        $this->assertEmailCampaignFind($emailCampaign);

        $session = $this->createMock(SessionInterface::class);
        $message = $this->createMock(MessageInterface::class);
        $message->expects($this->any())
            ->method('getBody')
            ->willReturn(json_encode(['email_campaign' => $emailCampaignId]));

        $this->logger->expects($this->once())
            ->method('notice')
            ->with('Email campaign with id 1 was not found');

        $this->assertEquals(EmailCampaignSendProcessor::REJECT, $this->processor->process($message, $session));
    }

    public function testProcess()
    {
        $emailCampaignId = 1;
        /** @var EmailCampaign $emailCampaign */
        $emailCampaign = $this->getEntity(EmailCampaign::class, ['id' => $emailCampaignId]);
        $this->assertEmailCampaignFind($emailCampaign);

        $session = $this->createMock(SessionInterface::class);
        $message = $this->createMock(MessageInterface::class);
        $message->expects($this->any())
            ->method('getBody')
            ->willReturn(json_encode(['email_campaign' => $emailCampaignId]));
        $message->expects($this->any())
            ->method('getMessageId')
            ->willReturn('MID');

        $this->logger->expects($this->never())
            ->method($this->anything());

        $sender = $this->createMock(EmailCampaignSender::class);
        $sender->expects($this->once())
            ->method('send');

        $this->senderBuilder->expects($this->once())
            ->method('getSender')
            ->with($emailCampaign)
            ->willReturn($sender);

        /** @var Job|\PHPUnit\Framework\MockObject\MockObject $job */
        $job = $this->createMock(Job::class);
        $this->jobRunner->expects($this->once())
            ->method('runUnique')
            ->willReturnCallback(
                function ($ownerId, $name, $closure) use ($job, $emailCampaignId) {
                    $this->assertEquals('MID', $ownerId);
                    $this->assertEquals(Topics::SEND_EMAIL_CAMPAIGN . ':' . $emailCampaignId, $name);

                    return $closure($this->jobRunner, $job);
                }
            );

        $this->assertEquals(EmailCampaignSendProcessor::ACK, $this->processor->process($message, $session));
    }

    private function assertEmailCampaignFind(EmailCampaign $emailCampaign = null): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('find')
            ->with(EmailCampaign::class, 1)
            ->willReturn($emailCampaign);

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(EmailCampaign::class)
            ->willReturn($em);
    }
}
