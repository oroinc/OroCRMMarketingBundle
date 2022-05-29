<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListItem;
use Oro\Bundle\MarketingListBundle\Model\MarketingListItemConnector;

class MarketingListItemConnectorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var MarketingListItemConnector */
    private $connector;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->connector = new MarketingListItemConnector($this->doctrine, $this->doctrineHelper);
    }

    public function testContactExisting()
    {
        $entityId = 42;
        $marketingList = $this->createMock(MarketingList::class);
        $marketingListItem = $this->assertContactedExisting($marketingList, $entityId);

        $this->assertEquals($marketingListItem, $this->connector->contact($marketingList, $entityId));
    }

    public function testContactNew()
    {
        $entityId = 42;
        $marketingList = $this->createMock(MarketingList::class);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['marketingList' => $marketingList, 'entityId' => $entityId])
            ->willReturn(null);
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with(MarketingListItem::class)
            ->willReturn($repository);

        $em = $this->createMock(ObjectManager::class);
        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(MarketingListItem::class));
        $this->doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->with(MarketingListItem::class)
            ->willReturn($em);

        $marketingListItem = $this->connector->contact($marketingList, $entityId);
        $this->assertInstanceOf(
            MarketingListItem::class,
            $marketingListItem
        );

        $this->assertEquals(1, $marketingListItem->getContactedTimes());
        $this->assertInstanceOf(\DateTime::class, $marketingListItem->getLastContactedAt());
    }

    public function testContactResultRow()
    {
        $entityId = 42;
        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->willReturn(\stdClass::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with(\stdClass::class)
            ->willReturn('id');

        $this->assertContactedExisting($marketingList, $entityId);
        $marketingListItem = $this->connector->contactResultRow($marketingList, ['id' => $entityId]);
        $this->assertInstanceOf(
            MarketingListItem::class,
            $marketingListItem
        );
    }

    public function testContactResultRowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Result row must contain identifier field');

        $entityId = 42;
        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->willReturn(\stdClass::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with(\stdClass::class)
            ->willReturn('id');
        $this->connector->contactResultRow($marketingList, ['some' => $entityId]);
    }

    public function assertContactedExisting(MarketingList $marketingList, int $entityId): MarketingListItem
    {
        $marketingListItem = $this->createMock(MarketingListItem::class);
        $marketingListItem->expects($this->once())
            ->method('contact');

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['marketingList' => $marketingList, 'entityId' => $entityId])
            ->willReturn($marketingListItem);
        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with(MarketingListItem::class)
            ->willReturn($repository);

        return $marketingListItem;
    }
}
