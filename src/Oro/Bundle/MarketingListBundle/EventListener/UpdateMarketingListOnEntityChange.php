<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerTrait;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;
use Psr\Log\LoggerInterface;

class UpdateMarketingListOnEntityChange implements OptionalListenerInterface
{
    use OptionalListenerTrait;

    const MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY = 'oro_marketing_list.allowed_entities';

    /**
     * @var object[]
     */
    private $classesToUpdate = [];

    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MarketingListAllowedClassesProvider
     */
    private $provider;

    public function __construct(
        MessageProducerInterface $messageProducer,
        LoggerInterface $logger,
        MarketingListAllowedClassesProvider $provider
    ) {
        $this->messageProducer = $messageProducer;
        $this->logger = $logger;
        $this->provider = $provider;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$this->enabled) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $allowedEntities = $this->getAllowedEntities();

        $this->scheduleClasses($uow->getScheduledEntityInsertions(), $allowedEntities);
        $this->scheduleClasses($uow->getScheduledEntityUpdates(), $allowedEntities);
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        foreach ($this->classesToUpdate as $class) {
            try {
                $this->messageProducer->send(
                    UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_MESSAGE,
                    [
                        'class' => $class
                    ]
                );
            } catch (MessageQueueTransportException $e) {
                $this->logger->error(
                    'There was an exception while trying create message.',
                    [
                        'messageTopic' => UpdateMarketingListProcessor::UPDATE_MARKETING_LIST_MESSAGE,
                        'currentlyProcessedClass' => $class,
                        'classesScheduledToUpdate' => $this->classesToUpdate,
                        'exception' => $e,
                    ]
                );
            }
        }

        $this->classesToUpdate = [];
    }

    /**
     * @param object[] $entities
     * @param string[] $allowedClasses
     */
    private function scheduleClasses(array $entities, array $allowedClasses)
    {
        foreach ($entities as $entity) {
            $entityClass = $this->getOriginalClassIfAllowed($entity, $allowedClasses);

            if ($entityClass === false) {
                continue;
            }

            if (!in_array($entityClass, $this->classesToUpdate)) {
                $this->classesToUpdate[] = $entityClass;
            }
        }
    }

    /**
     * @return string[]
     */
    private function getAllowedEntities(): array
    {
        return $this->provider->getList();
    }

    /**
     * @param object $entity
     * @param string[] $allowedClasses
     * @return bool|string
     */
    private function getOriginalClassIfAllowed($entity, array $allowedClasses)
    {
        foreach ($allowedClasses as $allowedClass) {
            if (is_a($entity, $allowedClass)) {
                return $allowedClass;
            }
        }

        return false;
    }
}
