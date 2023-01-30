<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListVirtualRelationProvider;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MarketingListVirtualRelationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var CacheInterface */
    private $arrayCache;

    /** @var MarketingListVirtualRelationProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->arrayCache = $this->createMock(CacheInterface::class);

        $this->provider = new MarketingListVirtualRelationProvider($this->doctrineHelper, $this->arrayCache);
    }

    /**
     * @dataProvider fieldDataProvider
     */
    public function testIsVirtualRelation(
        string $className,
        string $fieldName,
        ?MarketingList $marketingList,
        bool $supported
    ) {
        if ('marketingList_virtual' === $fieldName) {
            $this->assertRepositoryCall($className, $marketingList);
            $this->arrayCache->expects($this->once())
                ->method('get')
                ->with('oro_marketing_list.lists_by_entities')
                ->willReturnCallback(function ($cacheKey, $callback) {
                    $item = $this->createMock(ItemInterface::class);
                    return $callback($item);
                });
        } else {
            $this->doctrineHelper->expects($this->never())
                ->method('getEntityRepository');
        }
        $this->assertEquals($supported, $this->provider->isVirtualRelation($className, $fieldName));
    }

    public function fieldDataProvider(): array
    {
        $marketingList = $this->createMock(MarketingList::class);

        return [
            'incorrect class incorrect field' => ['stdClass', 'test', null, false],
            'incorrect class correct field' => [
                'stdClass',
                'marketingList_virtual',
                null,
                false
            ],
            'incorrect field' => ['stdClass', 'test', $marketingList, false],
            'correct' => ['stdClass', 'marketingList_virtual', $marketingList, true],
        ];
    }

    public function testGetVirtualRelationsNoRelations()
    {
        $className = 'stdClass';

        $this->arrayCache->expects($this->once())
            ->method('get')
            ->with('oro_marketing_list.lists_by_entities')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });
        $this->assertRepositoryCall($className, null);
        $result = $this->provider->getVirtualRelations($className);

        $this->doctrineHelper->expects($this->never())
            ->method('getSingleEntityIdentifierFieldName');
        $this->assertEmpty($result);
    }

    public function testGetVirtualRelations()
    {
        $className = 'stdClass';
        $marketingList = $this->createMock(MarketingList::class);

        $this->arrayCache->expects($this->once())
            ->method('get')
            ->with('oro_marketing_list.lists_by_entities')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });
        $this->assertRepositoryCall($className, $marketingList);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with($className)
            ->willReturn('id');

        $result = $this->provider->getVirtualRelations($className);
        $this->assertArrayHasKey('marketingList_virtual', $result);
    }

    public function relationsDataProvider(): array
    {
        $marketingList = $this->createMock(MarketingList::class);

        return [
            'incorrect class incorrect field' => ['stdClass', null, false],
            'correct' => ['stdClass', $marketingList, true],
        ];
    }

    /**
     * @dataProvider fieldDataProvider
     */
    public function tesGetVirtualRelationQueryUnsupportedClass(
        string $className,
        string $fieldName,
        MarketingList $marketingList,
        bool $supported
    ) {
        $this->assertRepositoryCall($className, $marketingList);
        if ($supported) {
            $this->doctrineHelper->expects($this->once())
                ->method('getSingleEntityIdentifierFieldName')
                ->with($className)
                ->willReturn('id');
        }

        $result = $this->provider->getVirtualRelationQuery($className, $fieldName);

        $this->assertNotEmpty($result);
    }

    private function assertRepositoryCall(string $className, ?MarketingList $marketingList)
    {
        $query = $this->createMock(AbstractQuery::class);

        $results = [];
        if ($marketingList) {
            $results[] = ['entity' => $className];
        }

        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($results);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('ml.entity')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('ml')
            ->willReturn($queryBuilder);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with(MarketingList::class)
            ->willReturn($repository);
    }

    public function testHasMarketingListMethodWithCache()
    {
        $this->arrayCache->expects($this->exactly(2))
            ->method('get')
            ->with('oro_marketing_list.lists_by_entities')
            ->willReturn([\stdClass::class => true]);

        $this->assertTrue($this->provider->hasMarketingList('stdClass'));
        $this->assertFalse($this->provider->hasMarketingList('nonExistingClass'));
    }

    /**
     * @dataProvider targetJoinAliasDataProvider
     */
    public function testGetTargetJoinAlias(?string $selectFieldName, string $expected)
    {
        $this->assertEquals(
            $expected,
            $this->provider->getTargetJoinAlias(null, null, $selectFieldName)
        );
    }

    public function targetJoinAliasDataProvider(): array
    {
        return [
            [null, 'marketingList_virtual'],
            ['', 'marketingList_virtual'],
            ['field', 'marketingList_virtual'],
            ['marketingList', 'marketingList_virtual'],
            ['marketingListItem', 'marketingListItems'],
            ['marketingListItems', 'marketingListItems'],
        ];
    }
}
