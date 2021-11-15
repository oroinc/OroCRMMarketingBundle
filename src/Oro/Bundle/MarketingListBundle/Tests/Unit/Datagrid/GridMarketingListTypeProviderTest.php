<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\MarketingListBundle\Datagrid\GridMarketingListTypeProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class GridMarketingListTypeProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var GridMarketingListTypeProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);

        $this->provider = new GridMarketingListTypeProvider($this->registry);
    }

    /**
     * @dataProvider typeChoicesDataProvider
     */
    public function testGetListTypeChoices(array $data, array $expected)
    {
        $repository = $this->createMock(ObjectRepository::class);

        $repository->expects($this->any())
            ->method('findBy')
            ->willReturn($data);

        $om = $this->createMock(ObjectManager::class);

        $om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(GridMarketingListTypeProvider::MARKETING_LIST_TYPE))
            ->willReturn($repository);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->equalTo(GridMarketingListTypeProvider::MARKETING_LIST_TYPE))
            ->willReturn($om);

        $this->assertEquals(
            $expected,
            $this->provider->getListTypeChoices()
        );
    }

    public function typeChoicesDataProvider(): array
    {
        return [
            [[], []],
            [
                [
                    $this->getMarketingListType(MarketingListType::TYPE_DYNAMIC, 'label1'),
                    $this->getMarketingListType(MarketingListType::TYPE_MANUAL, 'label2'),
                ],
                [
                    'label1' => MarketingListType::TYPE_DYNAMIC,
                    'label2' => MarketingListType::TYPE_MANUAL,
                ]
            ]
        ];
    }

    private function getMarketingListType(string $type, string $label): MarketingListType
    {
        $listType = new MarketingListType($type);
        $listType->setLabel($label);

        return $listType;
    }
}
