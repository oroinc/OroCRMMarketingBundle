<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\EventListener;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\EventListener\UpdateMarketingListOnEntityChange;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;

class UpdateMarketingListOnEntityChangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateMarketingListOnEntityChange
     */
    private $listener;

    /**
     * @var MessageProducerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageProducer;

    /**
     * @var EntityProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityProvider;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject
     */
    private $unitOfWork;

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    protected function setUp()
    {
        $this->messageProducer = $this->getMockBuilder(MessageProducerInterface::class)
            ->getMock();

        $this->entityProvider = $this->getMockBuilder(EntityProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $this->container->expects($this->any())
            ->method('get')
            ->with('oro_marketing_list.entity_provider.contact_information')
            ->willReturn($this->entityProvider);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->unitOfWork = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();

        $this->entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->unitOfWork);

        $this->cacheProvider = new ArrayCache();

        $this->listener = new UpdateMarketingListOnEntityChange(
            $this->messageProducer,
            $this->container,
            $this->logger,
            $this->cacheProvider
        );
    }

    public function testFlowWithoutCache()
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->entityProvider->expects($this->once())
            ->method('getEntities')
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

    public function testFlowWithCache()
    {
        $onFlushEventArgs = new OnFlushEventArgs($this->entityManager);
        $postFlushEventArgs = new PostFlushEventArgs($this->entityManager);

        $this->entityProvider->expects($this->never())
            ->method('getEntities');

        $this->unitOfWork->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($this->getScheduledEntityInsertions());

        $this->unitOfWork->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($this->getScheduledEntityUpdates());

        $this->cacheProvider->save(
            UpdateMarketingListOnEntityChange::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY,
            $this->getAllowedEntitiesCached()
        );

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
            ->method('getEntities')
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
     *
     * @param string $topic
     * @param array $message
     */
    public function assertTopicAndMessageAreValid($topic, $message)
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
            if ($allowedEntity['name'] === $message['class']) {
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
    private function getScheduledEntityInsertions()
    {
        return [
            new Segment(),
            new User(),
        ];
    }

    /**
     * @return object[]
     */
    private function getScheduledEntityUpdates()
    {
        return [
            new MarketingList(),
            new Organization(),
        ];
    }

    /**
     * @return array
     */
    private function getAllowedEntities()
    {
        return [
            ['name' => User::class],
            ['name' => Organization::class],
        ];
    }

    /**
     * @return array
     */
    private function getAllowedEntitiesCached()
    {
        return [
            User::class,
            Organization::class,
        ];
    }
}
