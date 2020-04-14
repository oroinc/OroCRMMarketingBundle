<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;

class ContactInformationColumnConstraintTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContactInformationColumnConstraint
     */
    protected $constraint;

    protected function setUp(): void
    {
        $this->constraint = new ContactInformationColumnConstraint();
    }

    public function testGetTargets()
    {
        $this->assertEquals(
            array(
                ContactInformationColumnConstraint::CLASS_CONSTRAINT,
                ContactInformationColumnConstraint::PROPERTY_CONSTRAINT
            ),
            $this->constraint->getTargets()
        );
    }

    public function testGetDefaultOption()
    {
        $this->assertEquals('field', $this->constraint->getDefaultOption());
    }

    public function testValidatedBy()
    {
        $this->assertNotEmpty($this->constraint->validatedBy());
    }
}
