<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CampaignBundle\Command\SendEmailCampaignsCommand;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSender;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Tester\CommandTester;

class SendEmailCampaignsCommandTest extends \PHPUnit\Framework\TestCase
{
    private ManagerRegistry|MockObject $registry;

    private EmailCampaignSenderBuilder|MockObject $emailCampaignSenderBuilder;

    private SendEmailCampaignsCommand $command;

    private EmailCampaignRepository|MockObject $emailCampaignRepository;

    private EntityManager|MockObject $entityManager;

    private EmailCampaignSender|MockObject $emailCampaignSender;

    #[\Override]
    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->emailCampaignSenderBuilder = $this->createMock(EmailCampaignSenderBuilder::class);
        $this->emailCampaignRepository = $this->createMock(EmailCampaignRepository::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->emailCampaignSender = $this->createMock(EmailCampaignSender::class);

        $this->command = new SendEmailCampaignsCommand(
            $this->registry,
            $this->emailCampaignSenderBuilder
        );
    }

    public function testShouldReturnNoEmailCampaignsFound()
    {
        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(EmailCampaign::class)
            ->willReturn($this->emailCampaignRepository);

        $this->emailCampaignRepository->expects(self::once())
            ->method('findEmailCampaignsToSend')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertStringContainsString('No email campaigns to send', $tester->getDisplay());
    }

    public function testShouldSendEmailForEmailCampaigns()
    {
        $firstEmailCampaign = (new EmailCampaign())->setName('First Campaign');
        $secondEmailCampaign = (new EmailCampaign())->setName('Second Campaign');

        $this->registry->expects(self::once())
            ->method('getManagerForClass')
            ->with(EmailCampaign::class)
            ->willReturn($this->entityManager);

        $this->registry->expects(self::once())
            ->method('getRepository')
            ->with(EmailCampaign::class)
            ->willReturn($this->emailCampaignRepository);

        $this->emailCampaignRepository->expects(self::once())
            ->method('findEmailCampaignsToSend')
            ->willReturn([
                $firstEmailCampaign,
                $secondEmailCampaign,
            ]);

        $this->emailCampaignSenderBuilder->expects(self::exactly(2))
            ->method('getSender')
            ->willReturn($this->emailCampaignSender);

        $this->emailCampaignSender->expects(self::exactly(2))
            ->method('send');

        $this->entityManager->expects(self::exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$firstEmailCampaign],
                [$secondEmailCampaign],
            );

        $this->entityManager->expects(self::once())
            ->method('flush');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertStringContainsString('Email campaigns to send: 2', $tester->getDisplay());
        self::assertStringContainsString('Sending email campaign: First Campaign', $tester->getDisplay());
        self::assertStringContainsString('Sending email campaign: Second Campaign', $tester->getDisplay());
        self::assertStringContainsString('Finished email campaigns sending', $tester->getDisplay());
        self::assertTrue($firstEmailCampaign->isSent());
        self::assertEquals(\DateTime::class, get_class($firstEmailCampaign->getSentAt()));
        self::assertTrue($secondEmailCampaign->isSent());
        self::assertEquals(\DateTime::class, get_class($secondEmailCampaign->getSentAt()));
    }
}
