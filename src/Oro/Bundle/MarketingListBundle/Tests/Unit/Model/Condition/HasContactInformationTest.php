<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\Condition\HasContactInformation;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Component\ConfigExpression\ContextAccessor;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\PropertyAccess\PropertyPath;

class HasContactInformationTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactInformationFieldsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldsProvider;

    /** @var HasContactInformation */
    private $condition;

    protected function setUp(): void
    {
        $this->fieldsProvider = $this->createMock(ContactInformationFieldsProvider::class);

        $this->condition = new HasContactInformation($this->fieldsProvider);
        $this->condition->setContextAccessor(new ContextAccessor());
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

    public function invalidOptionsDataProvider(): array
    {
        return [
            'no options' => [[]],
            'no marketing list option' => [['type' => 'test']]
        ];
    }

    /**
     * @dataProvider optionsDataProvider
     */
    public function testInitialize(array $options, string $expectedList, string $expectedType)
    {
        self::assertSame($this->condition, $this->condition->initialize($options));
        self::assertEquals($expectedList, ReflectionUtil::getPropertyValue($this->condition, 'marketingList'));
        self::assertEquals($expectedType, ReflectionUtil::getPropertyValue($this->condition, 'type'));
    }

    public function optionsDataProvider(): array
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
        $this->condition->evaluate([]);
    }

    public function testEvaluate()
    {
        $type = 'test';
        $marketingList = $this->createMock(MarketingList::class);
        $context = new \stdClass();
        $context->marketingList = $marketingList;
        $context->type = $type;

        $this->fieldsProvider->expects(self::once())
            ->method('getMarketingListTypedFields')
            ->with($marketingList, $type)
            ->willReturn(true);

        $options = [
            'marketing_list' => new PropertyPath('marketingList'),
            'type' => new PropertyPath('type')
        ];

        $this->condition->initialize($options);

        self::assertTrue($this->condition->evaluate($context));
    }
}
