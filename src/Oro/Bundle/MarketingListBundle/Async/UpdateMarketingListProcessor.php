<?php

namespace Oro\Bundle\MarketingListBundle\Async;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Event\UpdateMarketingListEvent;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Notifies listeners to update marketing lists by the specified class.
 */
class UpdateMarketingListProcessor implements MessageProcessorInterface, TopicSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const UPDATE_MARKETING_LIST_EVENT = 'oro_marketing_list.event.update_marketing_list';

    private DoctrineHelper $doctrineHelper;

    private EventDispatcherInterface $dispatcher;

    public function __construct(DoctrineHelper $doctrineHelper, EventDispatcherInterface $dispatcher)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->dispatcher = $dispatcher;

        $this->logger = new NullLogger();
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();
        $marketingLists = $this->findMarketingLists($messageBody[MarketingListUpdateTopic::CLASS_NAME]);

        if (count($marketingLists)) {
            $this->logger->info(
                'Marketing lists found for class. Notifying listeners.',
                [
                    'class' => $messageBody[MarketingListUpdateTopic::CLASS_NAME],
                    'marketingLists' => $marketingLists,
                ]
            );

            $this->dispatch($marketingLists);
        }

        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [MarketingListUpdateTopic::getName()];
    }

    /**
     * @param MarketingList[] $marketingLists
     */
    private function dispatch(array $marketingLists): void
    {
        $event = new UpdateMarketingListEvent();
        $event->setMarketingLists($marketingLists);

        $this->dispatcher->dispatch($event, self::UPDATE_MARKETING_LIST_EVENT);
    }

    /**
     * @param string $class
     * @return MarketingList[]
     */
    private function findMarketingLists(string $class): array
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
