<?php

namespace Oro\Bundle\MarketingListBundle\Async;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateMarketingListProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    const UPDATE_MARKETING_LIST_MESSAGE = 'oro_marketing_list.message_queue.job.update_marketing_list';
    const UPDATE_MARKETING_LIST_EVENT = 'oro_marketing_list.event.update_marketing_list';

    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = JSON::decode($message->getBody());

        if (!array_key_exists('class', $body)) {
            return self::REJECT;
        }

        $class = $body['class'];
        $marketingLists = $this->findMarketingLists($class);

        if (count($marketingLists)) {
            $this->logger->info(
                'Marketing lists found for class. Notifying listeners.',
                [
                    'class' => $class,
                    'marketingLists' => $marketingLists,
                ]
            );

            $this->dispatch($marketingLists);
        }

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [self::UPDATE_MARKETING_LIST_MESSAGE];
    }

    /**
     * @param MarketingList[] $marketingLists
     */
    private function dispatch(array $marketingLists)
    {
        $event = new UpdateMarketingListEvent();
        $event->setMarketingLists($marketingLists);

        $this->dispatcher->dispatch($event, self::UPDATE_MARKETING_LIST_EVENT);
    }

    /**
     * @param string $class
     * @return MarketingList[]
     */
    private function findMarketingLists($class)
    {
        return $this->doctrineHelper
            ->getEntityManager(MarketingList::class)
            ->getRepository(MarketingList::class)
            ->findBy([
                'type' => 'dynamic',
                'entity' => $class,
            ]);
    }
}
