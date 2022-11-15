<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\EventListener\UpdateMarketingListOnEntityChange;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateMarketingListOnEntityChangeTest extends TestCase
{
    private const FEATURE = 'marketing_list';

    private MessageProducerInterface|MockObject $messageProducer;

    private MarketingListAllowedClassesProvider|MockObject $entityProvider;

    private LoggerInterface|MockObject $logger;

    private EntityManagerInterface|MockObject $entityManager;

    private UnitOfWork|MockObject $unitOfWork;

    private UpdateMarketingListOnEntityChange $listener;

    private FeatureChecker|MockObject $featureChecker;

    protected function setUp(): void
    {
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
        $this->entityProvider = $this->createMock(MarketingListAllowedClassesProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->featureChecker = $this->createMock(FeatureChecker::class);

        $this->entityManager->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);

        $this->listener = new UpdateMarketingListOnEntityChange(
            $this->messageProducer,
            $this->logger,
            $this->entityProvider
        );
    }

    public function testOnFlushWithDisabledListener(): void
    {
        $args = $this->createMock(OnFlushEventArgs::class);
        $args->expects(self::never())
            ->method('getEntityManager');

        $this->messageProducer->expects(self::never())
            ->method('send');

        self::assertInstanceOf(OptionalListenerInterface::class, $this->listener);
        $this->listener->setEnabled(false);

        $this->listener->onFlush($args);
    }

    public function testFlow(): void
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->assertFeatureChecker();

        $this->entityProvider->expects(self::once())
            ->method('getList')
            ->willReturn($this->getAllowedEntities());

        $this->unitOfWork->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($this->getScheduledEntityInsertions());

        $this->unitOfWork->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($this->getScheduledEntityUpdates());

        $this->messageProducer->expects(self::exactly(2))
            ->method('send')
            ->willReturnCallback([$this, 'assertTopicAndMessageAreValid']);

        $this->listener->onFlush($onFlushEventArgs);
        $this->listener->postFlush($postFlushEventArgs);
    }

    public function testFlowFeatureDisabled(): void
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->assertFeatureChecker(false);

        $this->entityProvider->expects(self::never())
            ->method('getList');

        $this->unitOfWork->expects(self::never())
            ->method('getScheduledEntityInsertions');

        $this->unitOfWork->expects(self::never())
            ->method('getScheduledEntityUpdates');

        $this->messageProducer->expects(self::never())
            ->method('send');

        $this->listener->onFlush($onFlushEventArgs);
        $this->listener->postFlush($postFlushEventArgs);
    }

    public function testMessageProducerThrowException(): void
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->assertFeatureChecker();

        $this->entityProvider->expects(self::once())
            ->method('getList')
            ->willReturn($this->getAllowedEntities());

        $this->unitOfWork->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($this->getScheduledEntityInsertions());

        $this->unitOfWork->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($this->getScheduledEntityUpdates());

        $this->logger->expects(self::exactly(2))
            ->method('error');

        $this->messageProducer->expects(self::exactly(2))
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
        if ($topic !== MarketingListUpdateTopic::getName()) {
            self::fail(
                sprintf(
                    'Tried to put into queue message with wrong topic. Should be %s, got %s',
                    MarketingListUpdateTopic::getName(),
                    $topic
                )
            );
        }

        if (!is_array($message)) {
            self::fail('Queue message was not array');
        }

        if (!array_key_exists('class', $message)) {
            self::fail('No key "class" founded in a queue message');
        }

        if (in_array($message['class'], $this->getAllowedEntities(), true)) {
            return;
        }

        self::fail(
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

    protected function assertFeatureChecker(bool $toggle = true): void
    {
        $this->assertInstanceOf(FeatureToggleableInterface::class, $this->listener);

        $this->listener->setFeatureChecker($this->featureChecker);
        $this->listener->addFeature(self::FEATURE);

        $this->featureChecker->expects(self::once())
            ->method('isFeatureEnabled')
            ->with(self::FEATURE)
            ->willReturn($toggle);
    }
}
