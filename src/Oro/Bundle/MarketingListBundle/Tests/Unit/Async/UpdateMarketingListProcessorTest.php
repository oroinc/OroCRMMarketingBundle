<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListProcessorTest extends \PHPUnit\Framework\TestCase
{
    use LoggerAwareTraitTestTrait;

    private EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject $eventDispatcher;

    private EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject $repository;

    private UpdateMarketingListProcessor $processor;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects(self::any())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->processor = new UpdateMarketingListProcessor(
            $doctrineHelper,
            $this->eventDispatcher
        );
        $this->setUpLoggerMock($this->processor);
    }

    public function testProcess(): void
    {
        $message = $this->createMock(MessageInterface::class);
        $message->expects(self::any())
            ->method('getBody')
            ->willReturn([MarketingListUpdateTopic::CLASS_NAME => Order::class]);

        $marketingList = new MarketingList();
        ReflectionUtil::setId($marketingList, 1);
        $marketingList->setName('test');

        $this->repository->expects(self::once())
            ->method('findBy')
            ->willReturn([$marketingList]);

        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(UpdateMarketingListEvent::class),
                UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT
            );

        $this->loggerMock->expects(self::once())
            ->method('info');

        $this->processor->process($message, $this->createMock(SessionInterface::class));
    }
}
