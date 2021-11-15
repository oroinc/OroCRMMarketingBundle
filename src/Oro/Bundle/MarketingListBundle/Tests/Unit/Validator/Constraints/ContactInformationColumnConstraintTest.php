<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;
use Symfony\Component\Validator\Constraint;

class ContactInformationColumnConstraintTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactInformationColumnConstraint */
    private $constraint;

    protected function setUp(): void
    {
        $this->constraint = new ContactInformationColumnConstraint();
    }

    public function testGetTargets()
    {
        $this->assertEquals(
            [
                Constraint::CLASS_CONSTRAINT,
                Constraint::PROPERTY_CONSTRAINT
            ],
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
