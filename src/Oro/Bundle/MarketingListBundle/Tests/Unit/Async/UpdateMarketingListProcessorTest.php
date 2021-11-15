<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ReflectionUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $eventDispatcher;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var UpdateMarketingListProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepository::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->processor = new UpdateMarketingListProcessor(
            $doctrineHelper,
            $this->eventDispatcher,
            $this->logger
        );
    }

    public function testProcess()
    {
        $message = $this->createMock(MessageInterface::class);
        $message->expects($this->any())
            ->method('getBody')
            ->willReturn(JSON::encode(['class' => Order::class]));

        $marketingList = new MarketingList();
        ReflectionUtil::setId($marketingList, 1);
        $marketingList->setName('test');

        $this->repository->expects($this->once())
            ->method('findBy')
            ->willReturn([$marketingList]);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UpdateMarketingListEvent::class),
                UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT
            );

        $this->logger->expects($this->once())
            ->method('info');

        $this->processor->process($message, $this->createMock(SessionInterface::class));
    }
}
