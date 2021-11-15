<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use Oro\Bundle\TagBundle\Grid\TagsExtension;

class MarketingListProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var Manager|\PHPUnit\Framework\MockObject\MockObject */
    private $dataGridManager;

    /** @var MarketingListProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->dataGridManager = $this->createMock(Manager::class);

        $this->provider = new MarketingListProvider($this->dataGridManager);
    }

    private function getQueryBuilder(array $dqlParts = []): QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
    {
        $qb = $this->createMock(QueryBuilder::class);

        $select = new Select();
        $select->add('t0.test as c1');

        $dqlParts[] = ['select', [$select]];

        $qb->expects($this->any())
            ->method('getDQLPart')
            ->willReturnMap($dqlParts);

        return $qb;
    }

    /**
     * @dataProvider queryBuilderDataProvider
     */
    public function testGetMarketingListQueryBuilder(string $type)
    {
        $marketingList = $this->getMarketingList($type);
        $queryBuilder = $this->getQueryBuilder();
        $dataGrid = $this->getDataGrid();
        $this->assertGetQueryBuilder($marketingList, $queryBuilder, $dataGrid);

        $this->assertEquals($queryBuilder, $this->provider->getMarketingListQueryBuilder($marketingList));
        $expectedColumnInformation = ['testField' => 't0.test'];
        $this->assertEquals($expectedColumnInformation, $this->provider->getColumnInformation($marketingList));
    }

    public function queryBuilderDataProvider(): array
    {
        return [
            [MarketingListType::TYPE_MANUAL],
            [MarketingListType::TYPE_DYNAMIC],
            [MarketingListType::TYPE_STATIC],
        ];
    }

    /**
     * @dataProvider queryBuilderDataProvider
     */
    public function testGetMarketingListResultIterator(string $type)
    {
        if ($type === MarketingListType::TYPE_MANUAL) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ITEMS_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ITEMS_MIXIN;
        }

        $marketingList = $this->getMarketingList($type);
        $queryBuilder = $this->getQueryBuilder();
        $dataGrid = $this->getDataGrid();
        $config = $dataGrid->getConfig();
        $config->offsetSetByPath(DatagridConfiguration::DATASOURCE_SKIP_COUNT_WALKER_PATH, true);

        $this->assertGetQueryBuilder(
            $marketingList,
            $queryBuilder,
            $dataGrid,
            $mixin
        );

        $this->assertInstanceOf(\Iterator::class, $this->provider->getMarketingListResultIterator($marketingList));
    }

    /**
     * @dataProvider queryBuilderDataProvider
     */
    public function testGetMarketingListEntitiesQueryBuilder(string $type)
    {
        $marketingList = $this->getMarketingList($type);

        $from = $this->createMock(From::class);
        $from->expects($this->once())
            ->method('getAlias')
            ->willReturn('alias');
        $queryBuilder = $this->getQueryBuilder([['from', [$from]]]);
        $this->assertEntitiesQueryBuilder($queryBuilder, $marketingList, 'alias');

        $this->assertInstanceOf(
            QueryBuilder::class,
            $this->provider->getMarketingListEntitiesQueryBuilder($marketingList)
        );
    }

    /**
     * @dataProvider queryBuilderDataProvider
     */
    public function testGetEntitiesIterator(string $type)
    {
        $marketingList = $this->getMarketingList($type);

        $from = $this->createMock(From::class);
        $from->expects($this->once())
            ->method('getAlias')
            ->willReturn('alias');
        $queryBuilder = $this->getQueryBuilder([['from', [$from]]]);
        $queryBuilder->expects($this->once())
            ->method('addSelect')
            ->with('t0.test as testField');
        $this->assertEntitiesQueryBuilder($queryBuilder, $marketingList, 'alias');

        $this->assertInstanceOf(\Iterator::class, $this->provider->getEntitiesIterator($marketingList));
    }

    private function assertEntitiesQueryBuilder(
        QueryBuilder|\PHPUnit\Framework\MockObject\MockObject $queryBuilder,
        MarketingList $marketingList,
        string $alias
    ): void {
        if ($marketingList->isManual()) {
            $mixin = MarketingListProvider::MANUAL_RESULT_ENTITIES_MIXIN;
        } else {
            $mixin = MarketingListProvider::RESULT_ENTITIES_MIXIN;
        }

        $dataGrid = $this->getDataGrid();

        $queryBuilder->expects($this->exactly(2))
            ->method('resetDQLPart')
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with($alias)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with($alias . '.id')
            ->willReturnSelf();

        $this->assertGetQueryBuilder(
            $marketingList,
            $queryBuilder,
            $dataGrid,
            $mixin
        );
    }

    private function assertGetQueryBuilder(
        MarketingList $marketingList,
        QueryBuilder $queryBuilder,
        DatagridInterface|\PHPUnit\Framework\MockObject\MockObject $dataGrid,
        string $mixin = null
    ): void {
        $dataSource = $this->createMock(OrmDatasource::class);
        $dataSource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($queryBuilder);
        $dataGrid->expects($this->once())
            ->method('acceptDatasource')
            ->willReturn($dataSource);
        $dataGrid->expects($this->any())
            ->method('getDatasource')
            ->willReturn($dataSource);

        $parameters = [
            PagerInterface::PAGER_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true],
            TagsExtension::TAGS_ROOT_PARAM => [PagerInterface::DISABLED_PARAM => true],
        ];
        if ($mixin) {
            $parameters['grid-mixin'] = $mixin;
        }
        $this->dataGridManager->expects($this->atLeastOnce())
            ->method('getDatagrid')
            ->with(ConfigurationProvider::GRID_PREFIX . $marketingList->getId(), $parameters)
            ->willReturn($dataGrid);
    }

    private function getMarketingList(string $typeName): MarketingList
    {
        $type = $this->createMock(MarketingListType::class);
        $type->expects($this->any())
            ->method('getName')
            ->willReturn($typeName);

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->any())
            ->method('getType')
            ->willReturn($type);
        $marketingList->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $marketingList->expects($this->any())
            ->method('isManual')
            ->willReturn($typeName === MarketingListType::TYPE_MANUAL);

        return $marketingList;
    }

    private function getDataGrid(): DatagridInterface
    {
        $dataGrid = $this->createMock(DatagridInterface::class);

        $columnAliases = ['testField' => 'c1'];
        $config = DatagridConfiguration::createNamed('test', []);
        $config->offsetSetByPath('[source][query_config][column_aliases]', $columnAliases);

        $dataGrid->expects($this->any())
            ->method('getConfig')
            ->willReturn($config);

        return $dataGrid;
    }
}
