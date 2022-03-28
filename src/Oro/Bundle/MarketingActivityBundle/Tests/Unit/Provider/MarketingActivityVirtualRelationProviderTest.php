<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\Provider\MarketingActivityVirtualRelationProvider;

class MarketingActivityVirtualRelationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var EntityProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $entityProvider;

    /** @var MarketingActivityVirtualRelationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->entityProvider = $this->createMock(EntityProvider::class);

        $this->provider = new MarketingActivityVirtualRelationProvider($this->entityProvider);
    }

    /**
     * @dataProvider fieldDataProvider
     */
    public function testIsVirtualRelation(
        string $className,
        string $fieldName,
        bool $isIgnoredEntity,
        bool $expected
    ) {
        if ('marketingActivity' === $fieldName) {
            $this->entityProvider->expects($this->once())
                ->method('isIgnoredEntity')
                ->with($className)
                ->willReturn($isIgnoredEntity);
        } else {
            $this->entityProvider->expects($this->never())
                ->method('isIgnoredEntity');
        }
        $this->assertEquals($expected, $this->provider->isVirtualRelation($className, $fieldName));
    }

    /**
     * @dataProvider fieldDataProvider
     */
    public function testGetVirtualRelationQuery(
        string $className,
        string $fieldName,
        bool $isIgnoredEntity,
        bool $expected
    ) {
        $this->entityProvider->expects($this->once())
            ->method('isIgnoredEntity')
            ->with($className)
            ->willReturn($isIgnoredEntity);
        $result = $this->provider->getVirtualRelationQuery($className, $fieldName);
        if ($expected) {
            $this->assertNotEmpty($result);
        } else {
            $this->assertEmpty($result);
        }
    }

    public function fieldDataProvider(): array
    {
        return [
            'incorrect class incorrect field' => [\stdClass::class, 'test', true, false],
            'incorrect class correct field' => [\stdClass::class, 'marketingActivity', true, false],
            'correct class incorrect field' => [\stdClass::class, 'test', false, false],
            'correct class correct field' => [\stdClass::class, 'marketingActivity', false, true],
        ];
    }

    public function testGetVirtualRelationsForIgnoredEntity()
    {
        $className = \stdClass::class;

        $this->entityProvider->expects($this->once())
            ->method('isIgnoredEntity')
            ->with($className)
            ->willReturn(true);

        $this->assertSame([], $this->provider->getVirtualRelations($className));
    }

    public function testGetVirtualRelationsForNotIgnoredEntity()
    {
        $className = \stdClass::class;

        $this->entityProvider->expects($this->once())
            ->method('isIgnoredEntity')
            ->with($className)
            ->willReturn(false);

        $this->assertEquals(
            [
                'marketingActivity' => [
                    'label' => 'oro.marketingactivity.entity_label',
                    'relation_type' => 'oneToMany',
                    'related_entity_name' => MarketingActivity::class,
                    'query' => [
                        'join' => [
                            'left' => [
                                [
                                    'join' => MarketingActivity::class,
                                    'alias' => 'marketingActivity',
                                    'conditionType' => Join::WITH,
                                    'condition' => sprintf(
                                        'marketingActivity.entityClass = \'%s\''
                                        . ' AND marketingActivity.entityId = entity.id',
                                        $className
                                    )
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->provider->getVirtualRelations($className)
        );
    }

    public function testGetTargetJoinAlias()
    {
        $this->assertEquals('marketingActivity', $this->provider->getTargetJoinAlias(null, null, null));
    }
}
