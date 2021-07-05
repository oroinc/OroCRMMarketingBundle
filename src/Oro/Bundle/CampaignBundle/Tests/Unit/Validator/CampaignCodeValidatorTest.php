<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Validator;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Validator\CampaignCodeValidator;
use Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCode;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class CampaignCodeValidatorTest extends ConstraintValidatorTestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        return new CampaignCodeValidator($this->registry, $this->translator);
    }

    public function testValidateIncorrectInstance()
    {
        $value = new \stdClass();

        $this->registry->expects($this->never())
            ->method($this->anything());

        $constraint = new CampaignCode();
        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    public function testValidatePassed()
    {
        $codeHistory = $this->getCampaignCodeHistory();

        $repository = $this->createMock(CampaignRepository::class);
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCampaignBundle:CampaignCodeHistory')
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
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCampaignBundle:CampaignCodeHistory')
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
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCampaignBundle:CampaignCodeHistory')
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
