<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\MarketingListBundle\Provider\MarketingListItemVirtualFieldProvider;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListVirtualRelationProvider;

class MarketingListItemVirtualFieldProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListVirtualRelationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $relationProvider;

    /** @var MarketingListItemVirtualFieldProvider */
    private $fieldProvider;

    protected function setUp(): void
    {
        $this->relationProvider = $this->createMock(MarketingListVirtualRelationProvider::class);

        $this->fieldProvider = new MarketingListItemVirtualFieldProvider($this->relationProvider);
    }

    /**
     * @dataProvider virtualFieldDataProvider
     */
    public function testIsVirtualField(bool $hasMarketingList, string $fieldName, bool $expected)
    {
        $className = 'stdClass';

        $this->relationProvider->expects($this->any())
            ->method('hasMarketingList')
            ->with($className)
            ->willReturn($hasMarketingList);

        $this->assertEquals($expected, $this->fieldProvider->isVirtualField($className, $fieldName));
    }

    public function virtualFieldDataProvider(): array
    {
        return [
            [false, 'test', false],
            [false, MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES, false],
            [true, 'test', false],
            [true, MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES, true],
        ];
    }

    /**
     * @dataProvider fieldsDataProvider
     */
    public function testGetVirtualFields(bool $hasMarketingList, array $expected)
    {
        $className = 'stdClass';

        $this->relationProvider->expects($this->once())
            ->method('hasMarketingList')
            ->with($className)
            ->willReturn($hasMarketingList);

        $this->assertEquals($expected, $this->fieldProvider->getVirtualFields($className));
    }

    public function fieldsDataProvider(): array
    {
        return [
            [
                true,
                [
                    MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES,
                    MarketingListItemVirtualFieldProvider::FIELD_LAST_CONTACTED_AT
                ]
            ],
            [false, []]
        ];
    }

    public function testGetVirtualFieldQueryException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No virtual field found for stdClass::test');

        $this->fieldProvider->getVirtualFieldQuery('stdClass', 'test');
    }

    /**
     * @dataProvider queryDataProvider
     */
    public function testGetVirtualFieldQuery(string $fieldName, array $expected)
    {
        $className = 'stdClass';

        $definition = [
            'query' => [
                'join' => [
                    'left' => [
                        ['entity.field']
                    ]
                ]
            ]
        ];
        $this->relationProvider->expects($this->once())
            ->method('getRelationDefinition')
            ->with($className)
            ->willReturn($definition);

        $expected['join'] = $definition['query']['join'];
        $this->assertEquals($expected, $this->fieldProvider->getVirtualFieldQuery($className, $fieldName));
    }

    public function queryDataProvider(): array
    {
        $mliAlias = MarketingListVirtualRelationProvider::MARKETING_LIST_ITEM_RELATION_NAME;

        return [
            [
                MarketingListItemVirtualFieldProvider::FIELD_CONTACTED_TIMES,
                [
                    'select' => [
                        'expr' => $mliAlias . '.contactedTimes',
                        'label' => 'oro.marketinglist.marketinglistitem.contacted_times.label',
                        'return_type' => 'integer'
                    ]
                ]
            ],
            [
                MarketingListItemVirtualFieldProvider::FIELD_LAST_CONTACTED_AT,
                [
                    'select' => [
                        'expr' => $mliAlias . '.lastContactedAt',
                        'label' => 'oro.marketinglist.marketinglistitem.last_contacted_at.label',
                        'return_type' => 'datetime'
                    ],
                ]
            ]
        ];
    }
}
