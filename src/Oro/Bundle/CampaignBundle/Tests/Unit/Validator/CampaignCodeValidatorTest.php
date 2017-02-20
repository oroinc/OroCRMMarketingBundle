<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Validator;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCode;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CampaignBundle\Validator\CampaignCodeValidator;

class CampaignCodeValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var Constraint|\PHPUnit_Framework_MockObject_MockObject $constraint
     */
    protected $constraint;

    /**
     * @var CampaignCodeValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->constraint = $this->getMockBuilder(Constraint::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new CampaignCodeValidator($this->registry, $this->translator);
    }

    public function testValidateIncorrectInstance()
    {
        $value = new \stdClass();

        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $this->constraint);
    }

    /**
     * @dataProvider validatePassedData
     * @param $isFind
     */
    public function testValidatePassed($isFind)
    {
        if ($isFind) {
            $code = $this->getMockBuilder(CampaignCode::class)
                ->disableOriginalConstructor()
                ->getMock();
        } else {
            $code = null;
        }

        $repository = $this->getMockBuilder(CampaignRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCampaignBundle:CampaignCode')
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'test'])
            ->willReturn($code);

        $value = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $value->expects($this->once())
            ->method('getCode')
            ->willReturn('test');

        if ($isFind) {
            $campaign = $this->getMockBuilder(Campaign::class)
                ->disableOriginalConstructor()
                ->getMock();
            $code->expects($this->once())
                ->method('getCampaign')
                ->willReturn($campaign);
            $campaign->expects($this->once())
                ->method('getId')
                ->willReturn('1');
            $value->expects($this->once())
                ->method('getId')
                ->willReturn('1');
        }

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())
            ->method($this->anything());

        $this->validator->initialize($context);
        $this->validator->validate($value, $this->constraint);
    }

    public function validatePassedData()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testValidateFailed()
    {
        $code = $this->getMockBuilder(CampaignCode::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(CampaignRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCampaignBundle:CampaignCode')
            ->willReturn($repository);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => 'test'])
            ->willReturn($code);

        $value = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $value->expects($this->once())
            ->method('getCode')
            ->willReturn('test');

        $campaign = $this->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
        $code->expects($this->once())
            ->method('getCampaign')
            ->willReturn($campaign);
        $campaign->expects($this->once())
            ->method('getId')
            ->willReturn('1');
        $value->expects($this->once())
            ->method('getId')
            ->willReturn('2');

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock(ExecutionContextInterface::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($violation);
        $violation->expects($this->once())
            ->method('atPath')
            ->with('code')
            ->willReturnSelf();
        $violation->expects($this->once())
            ->method('addViolation');

        $this->validator->initialize($context);
        $this->validator->validate($value, $this->constraint);
    }
}
