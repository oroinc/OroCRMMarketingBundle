<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Validator;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;
use Oro\Bundle\MarketingListBundle\Validator\ContactInformationColumnValidator;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class ContactInformationColumnValidatorTest extends ConstraintValidatorTestCase
{
    /** @var ContactInformationFieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $contactInformationFieldHelper;

    protected function setUp(): void
    {
        $this->contactInformationFieldHelper = $this->createMock(ContactInformationFieldHelper::class);
        parent::setUp();
    }

    protected function createValidator()
    {
        return new ContactInformationColumnValidator($this->contactInformationFieldHelper);
    }

    public function testValidateFieldException()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "string", "array" given');

        $constraint = new ContactInformationColumnConstraint();
        $constraint->field = ['test'];

        $value = $this->getMockForAbstractClass(AbstractQueryDesigner::class);
        $this->validator->validate($value, $constraint);
    }

    public function testValidateValueException()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "AbstractQueryDesigner", "string" given');

        $constraint = new ContactInformationColumnConstraint();

        $value = 'test';
        $this->validator->validate($value, $constraint);
    }

    public function testValidateValid()
    {
        $constraint = new ContactInformationColumnConstraint();
        $value = $this->getMockForAbstractClass(AbstractQueryDesigner::class);

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationFields')
            ->with($value)
            ->willReturn(['email' => ['testField']]);

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @dataProvider validatorDataProvider
     */
    public function testValidateInvalidClass(?string $type, array $contactInformationFields)
    {
        $value = $this->getMockForAbstractClass(AbstractQueryDesigner::class);

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationFields')
            ->with($value)
            ->willReturn($contactInformationFields);

        $constraint = new ContactInformationColumnConstraint(['type' => $type]);
        $this->validator->validate($value, $constraint);

        if ($type) {
            $this->buildViolation($constraint->typeMessage)
                ->setParameter('%type%', $type)
                ->assertRaised();
        } else {
            $this->buildViolation($constraint->message)
                ->assertRaised();
        }
    }

    public function validatorDataProvider(): array
    {
        return [
            'no type' => [null, []],
            'type no fields' => ['email', []],
            'no typed fields' => ['email', ['phone' => ['test']]]
        ];
    }

    /**
     * @dataProvider validatorDataProvider
     */
    public function testValidateInvalidField(?string $type, array $contactInformationFields)
    {
        $value = new \stdClass();
        $value->test = $this->getMockForAbstractClass(AbstractQueryDesigner::class);

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationFields')
            ->with($value->test)
            ->willReturn($contactInformationFields);

        $constraint = new ContactInformationColumnConstraint(['type' => $type, 'field' => 'test']);
        $this->validator->validate($value, $constraint);

        if ($type) {
            $this->buildViolation($constraint->typeMessage)
                ->setParameter('%type%', $type)
                ->atPath('property.path.test')
                ->assertRaised();
        } else {
            $this->buildViolation($constraint->message)
                ->atPath('property.path.test')
                ->assertRaised();
        }
    }
}
