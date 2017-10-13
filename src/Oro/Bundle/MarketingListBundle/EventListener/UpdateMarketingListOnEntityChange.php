<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\MarketingListBundle\Async\UpdateMarketingListProcessor;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;

class UpdateMarketingListOnEntityChange
{
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

    /**
     * @param MessageProducerInterface $messageProducer
     * @param ContainerInterface $container This parameter is not used anymore
     * @param LoggerInterface $logger
     * @param CacheProvider $cacheProvider This parameter is not used anymore
     */
    public function __construct(
        MessageProducerInterface $messageProducer,
        ContainerInterface $container,
        LoggerInterface $logger,
        CacheProvider $cacheProvider
    ) {
        $this->messageProducer = $messageProducer;
        $this->logger = $logger;
    }

    /**
     * @param MarketingListAllowedClassesProvider $provider
     * @return $this
     */
    public function setMarketingListAllowedClassesProvider(MarketingListAllowedClassesProvider $provider)
    {
        $this->provider = $provider;

        return $this;
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
        if ($this->provider === null) {
            $this->logger->error(
                'Missing provider. Call UpdateMarketingListOnEntityChange::setMarketingListAllowedClassesProvider()',
                [
                    'classesScheduledToUpdate' => $this->classesToUpdate,
                    'class' => self::class,
                ]
            );

            return [];
        }

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

    /**
     * @return EntityProvider
     */
    private function getEntityProvider()
    {
        return $this->container->get('oro_marketing_list.entity_provider.contact_information');
    }
}
