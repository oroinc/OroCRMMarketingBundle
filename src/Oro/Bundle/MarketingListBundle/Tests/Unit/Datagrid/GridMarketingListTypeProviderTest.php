<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Datagrid;

use Oro\Bundle\MarketingListBundle\Datagrid\GridMarketingListTypeProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class GridMarketingListTypeProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var GridMarketingListTypeProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->registry = $this->createMock('Doctrine\Persistence\ManagerRegistry');

        $this->provider = new GridMarketingListTypeProvider($this->registry);
    }

    /**
     * @dataProvider typeChoicesDataProvider
     */
    public function testGetListTypeChoices(array $data, array $expected)
    {
        $repository = $this
            ->getMockBuilder('\Doctrine\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue($data));

        $om = $this
            ->getMockBuilder('Doctrine\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $om
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(GridMarketingListTypeProvider::MARKETING_LIST_TYPE))
            ->will($this->returnValue($repository));

        $this->registry
            ->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->equalTo(GridMarketingListTypeProvider::MARKETING_LIST_TYPE))
            ->will($this->returnValue($om));

        $this->assertEquals(
            $expected,
            $this->provider->getListTypeChoices()
        );
    }

    /**
     * @return array
     */
    public function typeChoicesDataProvider()
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

    /**
     * @param string $type
     * @param string $label
     *
     * @return MarketingListType
     */
    protected function getMarketingListType($type, $label)
    {
        $listType = new MarketingListType($type);

        return $listType->setLabel($label);
    }
}
