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
use Oro\Component\Testing\Unit\EntityTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListProcessorTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var UpdateMarketingListProcessor
     */
    private $processor;

    /**
     * @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $doctrineHelper;

    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;

    /**
     * @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->eventDispatcher = $this->getMockBuilder(EventDispatcherInterface::class)
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->processor = new UpdateMarketingListProcessor(
            $this->doctrineHelper,
            $this->eventDispatcher,
            $this->logger
        );
    }

    public function testProcess()
    {
        $message = $this->getMessage(JSON::encode(['class' => Order::class]));

        $this->repository->expects($this->once())
            ->method('findBy')
            ->willReturn([$this->getEntity(MarketingList::class, ['id' => 1, 'name' => 'test'])]);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UpdateMarketingListEvent::class),
                UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_EVENT
            );

        $this->logger->expects($this->once())
            ->method('info');

        $this->processor->process($message, $this->getSessionInterface());
    }

    /**
     * @return SessionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getSessionInterface()
    {
        return $this->getMockBuilder(SessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $body
     * @return MessageInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getMessage($body)
    {
        $message = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $message->expects($this->any())
            ->method('getBody')
            ->willReturn($body);
        return $message;
    }
}
