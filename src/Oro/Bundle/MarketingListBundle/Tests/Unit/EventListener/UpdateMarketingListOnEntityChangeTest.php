<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\EventListener\UpdateMarketingListOnEntityChange;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;
use Psr\Log\LoggerInterface;

class UpdateMarketingListOnEntityChangeTest extends \PHPUnit\Framework\TestCase
{
    /** @var MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $messageProducer;

    /** @var MarketingListAllowedClassesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $entityProvider;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var UnitOfWork|\PHPUnit\Framework\MockObject\MockObject */
    private $unitOfWork;

    /** @var UpdateMarketingListOnEntityChange */
    private $listener;

    protected function setUp(): void
    {
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
        $this->entityProvider = $this->createMock(MarketingListAllowedClassesProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);

        $this->listener = new UpdateMarketingListOnEntityChange(
            $this->messageProducer,
            $this->logger,
            $this->entityProvider
        );
    }

    public function testOnFlushWithDisabledListener()
    {
        $args = $this->createMock(OnFlushEventArgs::class);
        $args->expects($this->never())
            ->method('getEntityManager');

        $this->messageProducer->expects($this->never())
            ->method('send');

        $this->assertInstanceOf(OptionalListenerInterface::class, $this->listener);
        $this->listener->setEnabled(false);

        $this->listener->onFlush($args);
    }

    public function testFlow()
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->entityProvider->expects($this->once())
            ->method('getList')
            ->willReturn($this->getAllowedEntities());

        $this->unitOfWork->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($this->getScheduledEntityInsertions());

        $this->unitOfWork->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($this->getScheduledEntityUpdates());

        $this->messageProducer->expects($this->exactly(2))
            ->method('send')
            ->willReturnCallback([$this, 'assertTopicAndMessageAreValid']);

        $this->listener->onFlush($onFlushEventArgs);
        $this->listener->postFlush($postFlushEventArgs);
    }

    public function testMessageProducerThrowException()
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->entityProvider->expects($this->once())
            ->method('getList')
            ->willReturn($this->getAllowedEntities());

        $this->unitOfWork->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($this->getScheduledEntityInsertions());

        $this->unitOfWork->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($this->getScheduledEntityUpdates());

        $this->logger->expects($this->exactly(2))
            ->method('error');

        $this->messageProducer->expects($this->exactly(2))
            ->method('send')
            ->willThrowException(new MessageQueueTransportException());

        $this->listener->onFlush($onFlushEventArgs);
        $this->listener->postFlush($postFlushEventArgs);
    }

    /**
     * This method verifies if $topic is correct
     * and if message is array, with key "class" existing
     * and if classes that are allowed are pushed through that message
     */
    public function assertTopicAndMessageAreValid(string $topic, array $message): void
    {
        if ($topic !== UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_MESSAGE) {
            $this->fail(
                sprintf(
                    'Tried to put into queue message with wrong topic. Should be %s, got %s',
                    UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_MESSAGE,
                    $topic
                )
            );
        }

        if (!is_array($message)) {
            $this->fail('Queue message was not array');
        }

        if (!array_key_exists('class', $message)) {
            $this->fail('No key "class" founded in a queue message');
        }

        foreach ($this->getAllowedEntities() as $allowedEntity) {
            if ($allowedEntity === $message['class']) {
                return;
            }
        }

        $this->fail(
            sprintf(
                'Class %s should not be put into a queue.',
                $message['class']
            )
        );
    }

    /**
     * @return object[]
     */
    private function getScheduledEntityInsertions(): array
    {
        return [
            new Segment(),
            new User(),
        ];
    }

    /**
     * @return object[]
     */
    private function getScheduledEntityUpdates(): array
    {
        return [
            new MarketingList(),
            new Organization(),
        ];
    }

    /**
     * @return string[]
     */
    private function getAllowedEntities(): array
    {
        return [
            User::class,
            Organization::class,
        ];
    }
}
