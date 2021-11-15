<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;

class MarketingListHelperTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $managerRegistry;

    /** @var MarketingListHelper */
    private $helper;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->helper = new MarketingListHelper($this->managerRegistry);
    }

    /**
     * @dataProvider gridNameDataProvider
     */
    public function testGetMarketingListIdByGridName(string $grid, ?int $id)
    {
        $this->assertEquals($id, $this->helper->getMarketingListIdByGridName($grid));
    }

    public function gridNameDataProvider(): array
    {
        return [
            ['some_grid_1', null],
            [ConfigurationProvider::GRID_PREFIX, null],
            ['pre_' . ConfigurationProvider::GRID_PREFIX, null],
            [ConfigurationProvider::GRID_PREFIX . 1, 1],
            [ConfigurationProvider::GRID_PREFIX . '1_suffix', 1],
            [ConfigurationProvider::GRID_PREFIX . '1_suffix_2', 1],
            [ConfigurationProvider::GRID_PREFIX . '11_suffix_2', 11],
        ];
    }

    public function testGetMarketingList()
    {
        $id = 100;
        $entity = new \stdClass();

        $repository = $this->createMock(ObjectRepository::class);

        $repository->expects($this->once())
            ->method('find')
            ->with($id)
            ->willReturn($entity);

        $this->managerRegistry->expects($this->once())
            ->method('getRepository')
            ->with(MarketingListHelper::MARKETING_LIST)
            ->willReturn($repository);

        $this->assertEquals($entity, $this->helper->getMarketingList($id));
    }
}
