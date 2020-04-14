<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\Provider\MarketingActivityVirtualRelationProvider;

class MarketingActivityVirtualRelationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $doctrineHelper;

    /**
     * @var EntityProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityProvider;

    /**
     * @var MarketingActivityVirtualRelationProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityProvider = $this->getMockBuilder(EntityProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new MarketingActivityVirtualRelationProvider($this->doctrineHelper, $this->entityProvider);
    }

    /**
     * @dataProvider fieldDataProvider
     * @param string $className
     * @param string $fieldName
     * @param MarketingActivity|\PHPUnit\Framework\MockObject\MockObject $marketingActivity
     * @param bool $expected
     */
    public function testIsVirtualRelation($className, $fieldName, $marketingActivity, $expected)
    {
        if ('marketingActivity' === $fieldName) {
            $this->assertEntityProviderCall($className, $marketingActivity);
        } else {
            $this->entityProvider->expects($this->never())
                ->method('isIgnoredEntity');
        }
        $this->assertEquals($expected, $this->provider->isVirtualRelation($className, $fieldName));
    }

    /**
     * @dataProvider fieldDataProvider
     * @param string $className
     * @param string $fieldName
     * @param MarketingActivity|\PHPUnit\Framework\MockObject\MockObject $marketingActivity
     * @param bool $expected
     */
    public function testGetVirtualRelationQuery($className, $fieldName, $marketingActivity, $expected)
    {
        $this->assertEntityProviderCall($className, $marketingActivity);
        $result = $this->provider->getVirtualRelationQuery($className, $fieldName);
        if ($expected) {
            $this->assertNotEmpty($result);
        } else {
            $this->assertEmpty($result);
        }
    }

    /**
     * @return array
     */
    public function fieldDataProvider()
    {
        $className = 'stdClass';
        $marketingActivity = $this->getMockBuilder(MarketingActivity::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'incorrect class incorrect field' => [$className, 'test', null, false],
            'incorrect class correct field' => [$className, 'marketingActivity', null, false],
            'correct class incorrect field' => [$className, 'test', $marketingActivity, false],
            'correct class correct field' => [$className, 'marketingActivity', $marketingActivity, true],
        ];
    }

    /**
     * @dataProvider relationsDataProvider
     * @param string $className
     * @param MarketingActivity|\PHPUnit\Framework\MockObject\MockObject $marketingActivity
     * @param bool $expected
     */
    public function testGetVirtualRelations($className, $marketingActivity, $expected)
    {
        $this->assertEntityProviderCall($className, $marketingActivity);
        $result = $this->provider->getVirtualRelations($className);
        if ($expected) {
            $this->assertNotEmpty($result);
        } else {
            $this->assertEmpty($result);
        }
    }

    /**
     * @return array
     */
    public function relationsDataProvider()
    {
        $className = 'stdClass';
        $marketingActivity = $this->getMockBuilder(MarketingActivity::class)
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'incorrect class' => [$className, null, false],
            'correct class' => [$className, $marketingActivity, true],
        ];
    }

    public function testGetTargetJoinAlias()
    {
        $this->assertEquals('marketingActivity', $this->provider->getTargetJoinAlias(null, null, null));
    }

    public function testGetRelationDefinition()
    {
        $className = 'stdObject';

        $this->assertEquals(
            [
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
                                'condition' => "marketingActivity.entityClass = '{$className}'"
                                    . ' AND marketingActivity.entityId = entity.id'
                            ]
                        ]
                    ]
                ]
            ],
            $this->provider->getRelationDefinition($className)
        );
    }

    public function testHasMarketingActivityCachedResultOnSecondCallSameClass()
    {
        $marketingActivity = new MarketingActivity();
        $this->assertEntityProviderCall(\stdClass::class, $marketingActivity);

        $this->provider->hasMarketingActivity(\stdClass::class);
        $this->provider->hasMarketingActivity(\stdClass::class);
    }

    /**
     * @param string $className
     * @param MarketingActivity|\PHPUnit\Framework\MockObject\MockObject $marketingActivity
     */
    protected function assertEntityProviderCall($className, $marketingActivity)
    {
        $results = [];
        if ($marketingActivity) {
            $results[] = ['name' => $className];
        }

        $this->entityProvider->expects($this->once())
            ->method('isIgnoredEntity')
            ->with($className)
            ->willReturn(false);

        $this->entityProvider->expects($this->once())
            ->method('getEntity')
            ->with($className)
            ->will($this->returnValue($results));
    }
}
