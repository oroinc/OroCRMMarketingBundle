<?php

namespace Oro\Bundle\MarketingListBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MarketingListBundle\Async\Topic\MarketingListUpdateTopic;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerTrait;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception as MessageQueueTransportException;
use Psr\Log\LoggerInterface;

/**
 * Initiates marketing lists updates by sending {@see MarketingListUpdateTopic} MQ messages.
 */
class UpdateMarketingListOnEntityChange implements OptionalListenerInterface, FeatureToggleableInterface
{
    use OptionalListenerTrait, FeatureCheckerHolderTrait;

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

    public function onFlush(OnFlushEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!$this->isFeaturesEnabled()) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $allowedEntities = $this->getAllowedEntities();

        $this->scheduleClasses($uow->getScheduledEntityInsertions(), $allowedEntities);
        $this->scheduleClasses($uow->getScheduledEntityUpdates(), $allowedEntities);
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->classesToUpdate as $class) {
            try {
                $this->messageProducer->send(
                    MarketingListUpdateTopic::getName(),
                    [MarketingListUpdateTopic::CLASS_NAME => $class]
                );
            } catch (MessageQueueTransportException $e) {
                $this->logger->error(
                    'There was an exception while trying create message.',
                    [
                        'messageTopic' => MarketingListUpdateTopic::getName(),
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
    private function scheduleClasses(array $entities, array $allowedClasses): void
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
    private function getOriginalClassIfAllowed($entity, array $allowedClasses): bool|string
    {
        foreach ($allowedClasses as $allowedClass) {
            if (is_a($entity, $allowedClass)) {
                return $allowedClass;
            }
        }

        return false;
    }
}
