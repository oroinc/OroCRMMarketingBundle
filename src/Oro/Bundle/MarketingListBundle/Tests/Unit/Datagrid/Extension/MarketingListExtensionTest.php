<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Datagrid\Extension\MarketingListExtension;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Component\DoctrineUtils\ORM\Walker\UnionOutputResultModifier;

class MarketingListExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $marketingListHelper;

    /** @var Configuration|\PHPUnit\Framework\MockObject\MockObject */
    private $emConfiguration;

    /** @var MarketingListExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->marketingListHelper = $this->createMock(MarketingListHelper::class);

        $this->extension = new MarketingListExtension($this->marketingListHelper);
        $this->extension->setParameters(new ParameterBag());
    }

    public function testIsApplicableIncorrectDataSource()
    {
        $config = $this->createMock(DatagridConfiguration::class);
        $config->expects($this->once())
            ->method('isOrmDatasource')
            ->willReturn(false);

        $config->expects($this->any())
            ->method('getName')
            ->willReturn('grid');

        $this->assertFalse($this->extension->isApplicable($config));
    }

    public function testIsApplicableVisitTwice()
    {
        $config = $this->createMock(DatagridConfiguration::class);
        $config->expects($this->atLeastOnce())
            ->method('isOrmDatasource')
            ->willReturn(true);

        $config->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn(ConfigurationProvider::GRID_PREFIX . '1');

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with(ConfigurationProvider::GRID_PREFIX . '1')
            ->willReturn(1);

        $marketingList = new MarketingList();
        $marketingList->setSegment(new Segment())->setDefinition(json_encode(['filters' => ['filter' => 'dummy']]));

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $this->assertTrue($this->extension->isApplicable($config));

        $qb = $this->getQbMock();

        $dataSource = $this->createMock(OrmDatasource::class);

        $condition = new Andx();
        $condition->add('argument');

        $qb->expects($this->once())
            ->method('getDQLParts')
            ->willReturn(['where' => $condition]);

        $dataSource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        $this->extension->visitDatasource($config, $dataSource);
        $this->assertFalse($this->extension->isApplicable($config));
    }

    /**
     * @dataProvider applicableDataProvider
     */
    public function testIsApplicable(?int $marketingListId, ?object $marketingList, bool $expected)
    {
        $gridName = 'test_grid';
        $config = $this->assertIsApplicable($marketingListId, $marketingList, $gridName);

        $this->assertEquals($expected, $this->extension->isApplicable($config));
    }

    public function applicableDataProvider(): array
    {
        $nonManualMarketingList = $this->createMock(MarketingList::class);
        $nonManualMarketingList->expects($this->any())
            ->method('isUnion')
            ->willReturn(true);
        $nonManualMarketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(false);
        $nonManualMarketingList->expects($this->once())
            ->method('getDefinition')
            ->willReturn(json_encode(['filters' => ['filter' => 'dummy']]));

        $manualMarketingList = $this->createMock(MarketingList::class);
        $manualMarketingList->expects($this->any())
            ->method('isUnion')
            ->willReturn(true);
        $manualMarketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(true);
        $manualMarketingList->expects($this->never())
            ->method('getDefinition');

        $nonManualMarketingListWithoutFilters = $this->createMock(MarketingList::class);
        $nonManualMarketingListWithoutFilters->expects($this->any())
            ->method('isUnion')
            ->willReturn(true);
        $nonManualMarketingListWithoutFilters->expects($this->once())
            ->method('isManual')
            ->willReturn(false);
        $nonManualMarketingListWithoutFilters->expects($this->once())
            ->method('getDefinition')
            ->willReturn(json_encode(['filters' => []]));

        return [
            [null, null, false],
            [1, null, false],
            [2, $manualMarketingList, false],
            [3, $nonManualMarketingList, true],
            [4, $nonManualMarketingListWithoutFilters, false],
        ];
    }

    /**
     * @dataProvider dataSourceDataProvider
     */
    public function testVisitDatasource(array $dqlParts, bool $expected)
    {
        $marketingListId = 1;
        $nonManualMarketingList = $this->createMock(MarketingList::class);
        $nonManualMarketingList->expects($this->any())
            ->method('isUnion')
            ->willReturn(true);

        $nonManualMarketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(false);

        $nonManualMarketingList->expects($this->once())
            ->method('getDefinition')
            ->willReturn(json_encode(['filters' => ['filter' => 'dummy']]));

        $gridName = 'test_grid';
        $config = $this->assertIsApplicable($marketingListId, $nonManualMarketingList, $gridName);

        $dataSource = $this->createMock(OrmDatasource::class);

        $qb = $this->getQbMock();

        if (!empty($dqlParts['where'])) {
            /** @var Andx $where */
            $where = $dqlParts['where'];
            $parts = $where->getParts();

            $qb->expects($this->exactly(count($parts)))
                ->method('andWhere');

            $functionParts = array_filter(
                $parts,
                function ($part) {
                    return !is_string($part);
                }
            );

            if ($functionParts && $expected) {
                $this->emConfiguration->expects($this->exactly(2))
                    ->method('setDefaultQueryHint')
                    ->withConsecutive(
                        [UnionOutputResultModifier::HINT_UNION_KEY],
                        [UnionOutputResultModifier::HINT_UNION_VALUE]
                    );
            }
        }

        if ($expected) {
            $qb->expects($this->once())
                ->method('getDQLParts')
                ->willReturn($dqlParts);

            $dataSource->expects($this->once())
                ->method('getQueryBuilder')
                ->willReturn($qb);
        }

        $this->extension->visitDatasource($config, $dataSource);
    }

    private function assertIsApplicable(
        ?int $marketingListId,
        ?object $marketingList,
        string $gridName
    ): DatagridConfiguration {
        $config = $this->createMock(DatagridConfiguration::class);
        $config->expects($this->atLeastOnce())
            ->method('isOrmDatasource')
            ->willReturn(true);

        $config->expects($this->any())
            ->method('getName')
            ->willReturn($gridName);

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->willReturn($marketingListId);
        if ($marketingListId) {
            $this->marketingListHelper->expects($this->any())
                ->method('getMarketingList')
                ->with($marketingListId)
                ->willReturn($marketingList);
        } else {
            $this->marketingListHelper->expects($this->never())
                ->method('getMarketingList');
        }

        return $config;
    }

    /**
     * @return QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getQbMock()
    {
        $em = $this->createMock(EntityManager::class);
        $query = $this->createMock(AbstractQuery::class);
        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $qb->expects($this->any())
            ->method('leftJoin')
            ->willReturnSelf();
        $qb->expects($this->any())
            ->method('select')
            ->willReturnSelf();
        $qb->expects($this->any())
            ->method('andWhere')
            ->willReturnSelf();
        $qb->expects($this->any())
            ->method('expr')
            ->willReturn($this->createMock(Expr::class));
        $qb->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($em);
        $qb->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $this->emConfiguration = $this->createMock(Configuration::class);
        $em->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($this->emConfiguration);
        $em->expects($this->any())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        return $qb;
    }

    public function dataSourceDataProvider(): array
    {
        return [
            [['where' => []], true],
            [['where' => new Andx()], true],
            [['where' => new Andx(['test'])], true],
            [['where' => new Andx([new Func('func condition', ['argument'])])], true],
            [['where' => new Andx(['test', new Func('func condition', ['argument'])])], true]
        ];
    }

    public function testGetPriority()
    {
        $this->assertIsInt($this->extension->getPriority());
    }

    public function testIsApplicableSameGridTwiceWithParamsChangedUsingMQ()
    {
        $config = DatagridConfiguration::createNamed('grid1', ['param1' => false]);
        $config->setDatasourceType(OrmDatasource::TYPE);

        $configChanged = DatagridConfiguration::createNamed('grid1', ['param1' => true]);
        $configChanged->setDatasourceType(OrmDatasource::TYPE);

        $this->marketingListHelper->expects($this->exactly(2))
            ->method('getMarketingListIdByGridName');

        $this->assertFalse($this->extension->isApplicable($config));
        $this->assertFalse($this->extension->isApplicable($config));
        $this->assertFalse($this->extension->isApplicable($configChanged));
    }

    public function testVisitDatasourceIsNotApplicable()
    {
        $config = $this->assertIsApplicable(1, new MarketingList(), 'test_grid');

        $dataSource = $this->createMock(OrmDatasource::class);
        $dataSource->expects($this->never())
            ->method('getQueryBuilder');

        $this->extension->visitDatasource($config, $dataSource);
    }
}
