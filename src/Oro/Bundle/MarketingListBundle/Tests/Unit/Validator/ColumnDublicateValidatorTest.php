<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Validator;

use Oro\Bundle\MarketingListBundle\Validator\ColumnDublicateValidator;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ColumnDublicateValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Constraint|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $constraint;

    /**
     * @var ColumnDublicateValidator
     */
    protected $validator;

    /**
     * @var ExecutionContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    public function setUp()
    {
        $this->constraint = $this->getMockBuilder(Constraint::class)->getMock();
        $this->context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $this->validator = new ColumnDublicateValidator();
        $this->validator->initialize($this->context);
    }

    public function testValidateShouldAddViolation()
    {
        $this->context->expects($this->once())->method('addViolation');
        $segment = new Segment();
        $segment->setDefinition(json_encode([
            'columns' => [
                0 => [
                    'name' => 'Test',
                    'label' => 'Test',
                ],
                1 => [
                    'name' => 'Test',
                    'label' => 'Test',
                ]
            ]
        ]));
        $this->validator->validate($segment, $this->constraint);
    }

    public function testValidateShouldDoNothingIfNotSegment()
    {
        $this->context->expects($this->never())->method('addViolation');
        $this->validator->validate(null, $this->constraint);
    }

    public function testValidateShouldDoNothingIfColumnsNotDublicated()
    {
        $this->context->expects($this->never())->method('addViolation');
        $segment = new Segment();
        $segment->setDefinition(json_encode([
            'columns' => [
                0 => [
                    'name' => 'Test',
                ],
                1 => [
                    'name' => 'Test2',
                ]
            ]
        ]));
        $this->validator->validate($segment, $this->constraint);
    }
}
