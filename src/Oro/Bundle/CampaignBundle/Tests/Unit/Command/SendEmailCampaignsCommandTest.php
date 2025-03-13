<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Command\SendEmailCampaignsCommand;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSender;
use Oro\Bundle\CampaignBundle\Model\EmailCampaignSenderBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SendEmailCampaignsCommandTest extends TestCase
{
    private ManagerRegistry|MockObject $doctrine;
    private EmailCampaignSenderBuilder|MockObject $emailCampaignSenderBuilder;
    private SendEmailCampaignsCommand $command;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->emailCampaignSenderBuilder = $this->createMock(EmailCampaignSenderBuilder::class);

        $this->command = new SendEmailCampaignsCommand(
            $this->doctrine,
            $this->emailCampaignSenderBuilder
        );
    }

    public function testShouldReturnNoEmailCampaignsFound(): void
    {
        $emailCampaignRepository = $this->createMock(EmailCampaignRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(EmailCampaign::class)
            ->willReturn($emailCampaignRepository);
        $emailCampaignRepository->expects(self::once())
            ->method('findEmailCampaignsToSend')
            ->willReturn([]);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertStringContainsString('No email campaigns to send', $tester->getDisplay());
    }

    public function testShouldSendEmailForEmailCampaigns(): void
    {
        $firstEmailCampaign = (new EmailCampaign())->setName('First Campaign');
        $secondEmailCampaign = (new EmailCampaign())->setName('Second Campaign');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(EmailCampaign::class)
            ->willReturn($entityManager);

        $emailCampaignRepository = $this->createMock(EmailCampaignRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(EmailCampaign::class)
            ->willReturn($emailCampaignRepository);
        $emailCampaignRepository->expects(self::once())
            ->method('findEmailCampaignsToSend')
            ->willReturn([
                $firstEmailCampaign,
                $secondEmailCampaign,
            ]);

        $emailCampaignSender = $this->createMock(EmailCampaignSender::class);
        $this->emailCampaignSenderBuilder->expects(self::exactly(2))
            ->method('getSender')
            ->willReturn($emailCampaignSender);
        $emailCampaignSender->expects(self::exactly(2))
            ->method('send');

        $entityManager->expects(self::exactly(2))
            ->method('persist')
            ->withConsecutive(
                [$firstEmailCampaign],
                [$secondEmailCampaign],
            );

        $entityManager->expects(self::once())
            ->method('flush');

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        self::assertStringContainsString('Email campaigns to send: 2', $tester->getDisplay());
        self::assertStringContainsString('Sending email campaign: First Campaign', $tester->getDisplay());
        self::assertStringContainsString('Sending email campaign: Second Campaign', $tester->getDisplay());
        self::assertStringContainsString('Finished email campaigns sending', $tester->getDisplay());
        self::assertTrue($firstEmailCampaign->isSent());
        self::assertInstanceOf(\DateTime::class, $firstEmailCampaign->getSentAt());
        self::assertTrue($secondEmailCampaign->isSent());
        self::assertInstanceOf(\DateTime::class, $secondEmailCampaign->getSentAt());
    }
}
