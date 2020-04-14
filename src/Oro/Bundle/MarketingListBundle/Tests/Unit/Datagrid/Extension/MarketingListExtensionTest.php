<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid\Extension;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
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
    /** @var MarketingListExtension */
    protected $extension;

    /** @var \PHPUnit\Framework\MockObject\MockObject|MarketingListHelper */
    protected $marketingListHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    protected $em;

    /** @var \PHPUnit\Framework\MockObject\MockObject|Configuration */
    protected $emConfiguration;

    protected function setUp(): void
    {
        $this->marketingListHelper = $this->createMock(MarketingListHelper::class);

        $this->extension = new MarketingListExtension($this->marketingListHelper);
        $this->extension->setParameters(new ParameterBag());
    }

    public function testIsApplicableIncorrectDataSource()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DatagridConfiguration $config */
        $config = $this->createMock(DatagridConfiguration::class);
        $config
            ->expects($this->once())
            ->method('isOrmDatasource')
            ->will($this->returnValue(false));

        $config
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('grid'));

        $this->assertFalse($this->extension->isApplicable($config));
    }

    public function testIsApplicableVisitTwice()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|DatagridConfiguration $config */
        $config = $this->createMock(DatagridConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('isOrmDatasource')
            ->will($this->returnValue(true));

        $config
            ->expects($this->atLeastOnce())
            ->method('getName')
            ->will($this->returnValue(ConfigurationProvider::GRID_PREFIX . '1'));

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with(ConfigurationProvider::GRID_PREFIX . '1')
            ->will($this->returnValue(1));

        $marketingList = new MarketingList();
        $marketingList->setSegment(new Segment())->setDefinition(json_encode(['filters' => ['filter' => 'dummy']]));

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingList')
            ->with(1)
            ->willReturn($marketingList);

        $this->assertTrue($this->extension->isApplicable($config));

        $qb = $this->getQbMock();

        /** @var \PHPUnit\Framework\MockObject\MockObject|OrmDatasource $dataSource */
        $dataSource = $this->createMock(OrmDatasource::class);

        $condition = new Andx();
        $condition->add('argument');

        $qb
            ->expects($this->once())
            ->method('getDQLParts')
            ->will($this->returnValue(['where' => $condition]));

        $dataSource
            ->expects($this->once())
            ->method('getQueryBuilder')
            ->will($this->returnValue($qb));

        $this->extension->visitDatasource($config, $dataSource);
        $this->assertFalse($this->extension->isApplicable($config));
    }

    /**
     * @dataProvider applicableDataProvider
     *
     * @param int|null    $marketingListId
     * @param object|null $marketingList
     * @param bool        $expected
     */
    public function testIsApplicable($marketingListId, $marketingList, $expected)
    {
        $gridName = 'test_grid';
        $config   = $this->assertIsApplicable($marketingListId, $marketingList, $gridName);

        $this->assertEquals($expected, $this->extension->isApplicable($config));
    }

    /**
     * @return array
     */
    public function applicableDataProvider()
    {
        $nonManualMarketingList = $this->createMock(MarketingList::class);
        $nonManualMarketingList->expects($this->any())->method('isUnion')->will($this->returnValue(true));
        $nonManualMarketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));
        $nonManualMarketingList->expects($this->once())
            ->method('getDefinition')
            ->willReturn(json_encode(['filters' => ['filter' => 'dummy']]));

        $manualMarketingList = $this->createMock(MarketingList::class);
        $manualMarketingList->expects($this->any())->method('isUnion')->will($this->returnValue(true));
        $manualMarketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));
        $manualMarketingList->expects($this->never())
            ->method('getDefinition');

        $nonManualMarketingListWithoutFilters = $this->createMock(MarketingList::class);
        $nonManualMarketingListWithoutFilters->expects($this->any())->method('isUnion')->will($this->returnValue(true));
        $nonManualMarketingListWithoutFilters->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));
        $nonManualMarketingListWithoutFilters->expects($this->once())
            ->method('getDefinition')->willReturn(json_encode(['filters' => []]));

        return [
            [null, null, false],
            [1, null, false],
            [2, $manualMarketingList, false],
            [3, $nonManualMarketingList, true],
            [4, $nonManualMarketingListWithoutFilters, false],
        ];
    }

    /**
     * @param array $dqlParts
     * @param bool  $expected
     *
     * @dataProvider dataSourceDataProvider
     */
    public function testVisitDatasource($dqlParts, $expected)
    {
        $marketingListId        = 1;
        $nonManualMarketingList = $this->createMock(MarketingList::class);
        $nonManualMarketingList->expects($this->any())->method('isUnion')->will($this->returnValue(true));

        $nonManualMarketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));

        $nonManualMarketingList->expects($this->once())->method('getDefinition')->willReturn(
            json_encode(['filters' => ['filter' => 'dummy']])
        );

        $gridName = 'test_grid';
        $config   = $this->assertIsApplicable($marketingListId, $nonManualMarketingList, $gridName);

        /** @var \PHPUnit\Framework\MockObject\MockObject|OrmDatasource $dataSource */
        $dataSource = $this->createMock(OrmDatasource::class);

        $qb = $this->getQbMock();

        if (!empty($dqlParts['where'])) {
            /** @var Andx $where */
            $where = $dqlParts['where'];
            $parts = $where->getParts();

            $qb
                ->expects($this->exactly(count($parts)))
                ->method('andWhere');

            $functionParts = array_filter(
                $parts,
                function ($part) {
                    return !is_string($part);
                }
            );

            if ($functionParts && $expected) {
                $this->emConfiguration
                    ->expects($this->exactly(2))
                    ->method('setDefaultQueryHint')
                    ->withConsecutive(
                        [UnionOutputResultModifier::HINT_UNION_KEY],
                        [UnionOutputResultModifier::HINT_UNION_VALUE]
                    );
            }
        }

        if ($expected) {
            $qb
                ->expects($this->once())
                ->method('getDQLParts')
                ->will($this->returnValue($dqlParts));

            $dataSource
                ->expects($this->once())
                ->method('getQueryBuilder')
                ->will($this->returnValue($qb));
        }

        $this->extension->visitDatasource($config, $dataSource);
    }

    /**
     * @param int|null    $marketingListId
     * @param object|null $marketingList
     * @param string      $gridName
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|DatagridConfiguration
     */
    protected function assertIsApplicable($marketingListId, $marketingList, $gridName)
    {
        $config = $this->createMock(DatagridConfiguration::class);
        $config
            ->expects($this->atLeastOnce())
            ->method('isOrmDatasource')
            ->will($this->returnValue(true));

        $config
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($gridName));

        $this->marketingListHelper->expects($this->any())
            ->method('getMarketingListIdByGridName')
            ->with($gridName)
            ->will($this->returnValue($marketingListId));
        if ($marketingListId) {
            $this->marketingListHelper->expects($this->any())
                ->method('getMarketingList')
                ->with($marketingListId)
                ->will($this->returnValue($marketingList));
        } else {
            $this->marketingListHelper->expects($this->never())
                ->method('getMarketingList');
        }

        return $config;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|QueryBuilder
     */
    protected function getQbMock()
    {
        $this->em = $this->createMock(EntityManager::class);

        $qb = $this
            ->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs([$this->em])
            ->getMock();

        $qb
            ->expects($this->any())
            ->method('from')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('leftJoin')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('select')
            ->will($this->returnSelf());

        $qb
            ->expects($this->any())
            ->method('andWhere')
            ->will($this->returnSelf());

        $expr = $this
            ->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();

        $qb
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($expr));

        $qb->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));

        $query = $this->createMock(AbstractQuery::class);
        $qb->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $this->emConfiguration = $this->createMock(Configuration::class);
        $this->em->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($this->emConfiguration));
        $this->em->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($qb));

        return $qb;
    }

    /**
     * @return array
     */
    public function dataSourceDataProvider()
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

        $this->marketingListHelper->expects($this->exactly(2))->method('getMarketingListIdByGridName');

        $this->assertFalse($this->extension->isApplicable($config));
        $this->assertFalse($this->extension->isApplicable($config));
        $this->assertFalse($this->extension->isApplicable($configChanged));
    }

    public function testVisitDatasourceIsNotApplicable()
    {
        $config = $this->assertIsApplicable(1, new MarketingList(), 'test_grid');

        /** @var \PHPUnit\Framework\MockObject\MockObject|OrmDatasource $dataSource */
        $dataSource = $this->createMock(OrmDatasource::class);
        $dataSource->expects($this->never())
            ->method('getQueryBuilder');

        $this->extension->visitDatasource($config, $dataSource);
    }
}
