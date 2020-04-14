<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Validator;

use Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;
use Oro\Bundle\MarketingListBundle\Validator\ContactInformationColumnValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ContactInformationColumnValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contactInformationFieldHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var ContactInformationColumnValidator
     */
    protected $validator;

    protected function setUp(): void
    {
        $this->contactInformationFieldHelper = $this
            ->getMockBuilder('Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->createMock(ExecutionContextInterface::class);

        $this->validator = new ContactInformationColumnValidator($this->contactInformationFieldHelper);
        $this->validator->initialize($this->context);
    }

    public function testValidateFieldException()
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "array" given');

        $constraint = new ContactInformationColumnConstraint();
        $constraint->field = ['test'];

        $value = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');
        $this->validator->validate($value, $constraint);
    }

    public function testValidateValueException()
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "AbstractQueryDesigner", "string" given');

        $constraint = new ContactInformationColumnConstraint();

        $value = 'test';
        $this->validator->validate($value, $constraint);
    }

    public function testValidateValid()
    {
        $constraint = new ContactInformationColumnConstraint();
        $value = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationFields')
            ->with($value)
            ->will($this->returnValue(['email' => ['testField']]));

        $this->context->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider validatorDataProvider
     * @param string $type
     * @param array $contactInformationFields
     */
    public function testValidateInvalidClass($type, array $contactInformationFields)
    {
        $constraint = new ContactInformationColumnConstraint();
        $constraint->type = $type;
        $value = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationFields')
            ->with($value)
            ->will($this->returnValue($contactInformationFields));

        $parameters = [];
        if ($type) {
            $message = $constraint->typeMessage;
            $parameters['%type%'] = $type;
        } else {
            $message = $constraint->message;
        }

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($message, $parameters);

        $this->validator->validate($value, $constraint);
    }

    /**
     * @return array
     */
    public function validatorDataProvider()
    {
        return [
            'no type' => [null, []],
            'type no fields' => ['email', []],
            'no typed fields' => ['email', ['phone' => ['test']]]
        ];
    }

    /**
     * @dataProvider validatorDataProvider
     * @param string $type
     * @param array $contactInformationFields
     */
    public function testValidateInvalidField($type, array $contactInformationFields)
    {
        $constraint = new ContactInformationColumnConstraint();
        $constraint->field = 'test';
        $constraint->type = $type;
        $value = new \stdClass();
        $value->test = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationFields')
            ->with($value->test)
            ->will($this->returnValue($contactInformationFields));

        $parameters = [];
        if ($type) {
            $message = $constraint->typeMessage;
            $parameters['%type%'] = $type;
        } else {
            $message = $constraint->message;
        }

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($message)
            ->willReturn($builder);
        $builder->expects($this->once())
            ->method('atPath')
            ->with($constraint->field)
            ->willReturnSelf();
        $builder->expects($this->once())
            ->method('setParameters')
            ->with($parameters)
            ->willReturnSelf();
        $builder->expects($this->once())
            ->method('addViolation');

        $this->validator->validate($value, $constraint);
    }
}
