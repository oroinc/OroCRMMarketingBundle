<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\TrackingBundle\Entity\UniqueTrackingVisit;
use Oro\Bundle\TrackingBundle\Migration\FillUniqueTrackingVisitsQuery;
use Oro\Bundle\TrackingBundle\Tools\UniqueTrackingVisitDumper;
use Psr\Log\LoggerInterface;

class UniqueTrackingVisitDumperTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var FillUniqueTrackingVisitsQuery|\PHPUnit\Framework\MockObject\MockObject */
    private $fillQuery;

    /** @var UniqueTrackingVisitDumper */
    private $dumper;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fillQuery = $this->createMock(FillUniqueTrackingVisitsQuery::class);

        $this->dumper = new UniqueTrackingVisitDumper($this->registry, $this->logger, $this->fillQuery);
    }

    public function testRefreshAggregatedDataException()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('beginTransaction');

        $connection = $this->createMock(Connection::class);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(UniqueTrackingVisit::class)
            ->willReturn($em);
        $this->registry->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $this->fillQuery->expects($this->once())
            ->method('setConnection')
            ->with($connection);

        $exception = new \Exception('Test');
        $this->fillQuery->expects($this->once())
            ->method('execute')
            ->with($this->logger)
            ->willThrowException($exception);
        $em->expects($this->once())
            ->method('rollback');
        $this->logger->expects($this->once())
            ->method('error')
            ->with('Tracking visit aggregation failed', ['exception' => $exception]);

        $this->assertFalse($this->dumper->refreshAggregatedData());
    }

    public function testRefreshAggregatedData()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('beginTransaction');

        $connection = $this->createMock(Connection::class);
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(UniqueTrackingVisit::class)
            ->willReturn($em);
        $this->registry->expects($this->once())
            ->method('getConnection')
            ->willReturn($connection);
        $this->fillQuery->expects($this->once())
            ->method('setConnection')
            ->with($connection);

        $this->fillQuery->expects($this->once())
            ->method('execute')
            ->with($this->logger);
        $em->expects($this->once())
            ->method('commit');
        $this->logger->expects($this->never())
            ->method($this->anything());

        $this->assertTrue($this->dumper->refreshAggregatedData());
    }
}
