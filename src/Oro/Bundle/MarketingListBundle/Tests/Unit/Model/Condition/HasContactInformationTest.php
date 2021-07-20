<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\Condition\HasContactInformation;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\PropertyAccess\PropertyPath;

class HasContactInformationTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContextAccessor|MockObject */
    protected $contextAccessor;

    /** @var ContactInformationFieldsProvider|MockObject */
    protected $fieldsProvider;

    /** @var HasContactInformation */
    protected $condition;

    protected function setUp(): void
    {
        $this->contextAccessor = new ContextAccessor();
        $this->fieldsProvider = $this->getMockBuilder(ContactInformationFieldsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->condition = new class($this->fieldsProvider) extends HasContactInformation {
            public function xgetMarketingList()
            {
                return $this->marketingList;
            }

            public function xgetType()
            {
                return $this->type;
            }
        };

        $this->condition->setContextAccessor($this->contextAccessor);
    }

    /**
     * @dataProvider invalidOptionsDataProvider
     */
    public function testInitializeException(array $options)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option "marketing_list" is required');

        $this->condition->initialize($options);
    }

    /**
     * @return array
     */
    public function invalidOptionsDataProvider()
    {
        return [
            'no options' => [[]],
            'no marketing list option' => [['type' => 'test']]
        ];
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param mixed $expectedList
     * @param mixed $expectedType
     */
    public function testInitialize($options, $expectedList, $expectedType)
    {
        static::assertSame($this->condition, $this->condition->initialize($options));
        static::assertEquals($expectedList, $this->condition->xgetMarketingList());
        static::assertEquals($expectedType, $this->condition->xgetType());
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        return [
            'named' => [
                [
                    'marketing_list' => 'ML',
                    'type' => 'type'
                ],
                'ML',
                'type'
            ],
            'indexed' => [
                [
                    'ML',
                    'type'
                ],
                'ML',
                'type'
            ],
        ];
    }

    public function testEvaluateException()
    {
        $this->expectException(InvalidArgumentException::class);
        $context = [];
        $this->condition->evaluate($context);
    }

    public function testEvaluate()
    {
        $type = 'test';
        $marketingList = $this->getMockBuilder(MarketingList::class)->disableOriginalConstructor()->getMock();
        $context = new \stdClass();
        $context->marketingList = $marketingList;
        $context->type = $type;

        $this->fieldsProvider->expects(static::once())
            ->method('getMarketingListTypedFields')
            ->with($marketingList, $type)
            ->willReturn(true);

        $options = [
            'marketing_list' => new PropertyPath('marketingList'),
            'type' => new PropertyPath('type')
        ];

        $this->condition->initialize($options);

        static::assertTrue($this->condition->evaluate($context));
    }
}
