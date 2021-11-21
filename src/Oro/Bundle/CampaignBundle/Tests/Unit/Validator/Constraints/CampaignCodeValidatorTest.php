<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCode;
use Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCodeValidator;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CampaignCodeValidatorTest extends ConstraintValidatorTestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        return new CampaignCodeValidator($this->doctrine);
    }

    public function testValidateIncorrectInstance()
    {
        $value = new \stdClass();

        $this->doctrine->expects($this->never())
            ->method($this->anything());

        $constraint = new CampaignCode();
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function testValidatePassed()
    {
        $codeHistory = $this->getCampaignCodeHistory();

        $repository = $this->createMock(CampaignRepository::class);
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with(CampaignCodeHistory::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'test'])
            ->willReturn($codeHistory);

        $value = $this->createMock(Campaign::class);
        $value->expects($this->once())
            ->method('getCode')
            ->willReturn('test');
        $value->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $constraint = new CampaignCode();
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function testValidateFailed()
    {
        $codeHistory = $this->getCampaignCodeHistory();

        $repository = $this->createMock(CampaignRepository::class);
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with(CampaignCodeHistory::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'test'])
            ->willReturn($codeHistory);

        $value = $this->createMock(Campaign::class);
        $value->expects($this->once())
            ->method('getCode')
            ->willReturn('test');
        $value->expects($this->once())
            ->method('getId')
            ->willReturn('2');

        $constraint = new CampaignCode();
        $this->validator->validate($value, $constraint);

        $this->buildViolation($constraint->message)
            ->atPath('property.path.code')
            ->assertRaised();
    }

    public function testValidateCodeHistoryNotFound()
    {
        $repository = $this->createMock(CampaignRepository::class);
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with(CampaignCodeHistory::class)
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'test'])
            ->willReturn(null);

        $value = $this->createMock(Campaign::class);
        $value->expects($this->once())
            ->method('getCode')
            ->willReturn('test');

        $constraint = new CampaignCode();
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    private function getCampaignCodeHistory(): CampaignCodeHistory
    {
        $campaign = new Campaign();
        ReflectionUtil::setId($campaign, 1);

        $campaignCodeHistory = new CampaignCodeHistory();
        ReflectionUtil::setId($campaignCodeHistory, 1);
        $campaignCodeHistory->setCode('test');
        $campaignCodeHistory->setCampaign($campaign);

        return $campaignCodeHistory;
    }
}
