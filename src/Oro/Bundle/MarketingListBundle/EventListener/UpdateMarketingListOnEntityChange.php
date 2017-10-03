<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;

class UpdateMarketingListOnEntityChange
{
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
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @param MessageProducerInterface $messageProducer
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        ContainerInterface $container,
        LoggerInterface $logger,
        CacheProvider $cacheProvider
    ) {
        $this->messageProducer = $messageProducer;
        $this->container = $container;
        $this->logger = $logger;
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $allowedEntities = $this->getAllowedEntities();

        $this->scheduleClasses($uow->getScheduledEntityInsertions(), $allowedEntities);
        $this->scheduleClasses($uow->getScheduledEntityUpdates(), $allowedEntities);
    }

    /**
     * @param PostFlushEventArgs $args
     */
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
    private function getAllowedEntities()
    {
        if (!$this->cacheProvider->contains(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY)) {
            $allowedEntities = $this->getEntityProvider()->getEntities(false);

            foreach ($allowedEntities as $i => $allowedEntity) {
                $allowedEntities[$i] = $allowedEntity['name'];
            }

            $this->cacheProvider->save(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY, $allowedEntities);

            return $allowedEntities;
        }

        return $this->cacheProvider->fetch(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY);
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

    /**
     * @return EntityProvider
     */
    private function getEntityProvider()
    {
        return $this->container->get('oro_marketing_list.entity_provider.contact_information');
    }
}
