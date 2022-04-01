<?php

namespace Oro\Bundle\TrackingBundle\Processor;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\TrackingBundle\Entity\Repository\TrackingEventRepository;
use Oro\Bundle\TrackingBundle\Entity\Repository\TrackingVisitEventRepository;
use Oro\Bundle\TrackingBundle\Entity\Repository\TrackingVisitRepository;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentificationProvider;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processes (parses) tracking logs.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class TrackingProcessor extends AbstractTrackingProcessor
{
    private DeviceDetectorFactory $deviceDetector;
    private TrackingEventIdentificationProvider $trackingIdentification;
    private ValidatorInterface $validator;

    private array $collectedVisits = [];
    private array $eventDictionary = [];
    private array $skipList = [];
    private int $processedBatches = 0;

    public function __construct(
        ManagerRegistry $doctrine,
        TrackingEventIdentificationProvider $trackingIdentification,
        ValidatorInterface $validator
    ) {
        parent::__construct($doctrine);
        $this->trackingIdentification = $trackingIdentification;
        $this->validator = $validator;
        $this->deviceDetector = new DeviceDetectorFactory();
    }

    public function process(): void
    {
        $this->turnOffDoctrineLogger();

        $this->checkNewVisits();
        $this->recheckPreviousVisitIdentifiers();
        $this->recheckPreviousVisitEvents();

        $this->logger?->info('<info>Done</info>');
    }

    private function checkNewVisits(): void
    {
        $this->logger?->info('Check new visits...');
        $totalEvents = $this->getTrackingEventsCount();
        if ($totalEvents) {
            $message = '<info>Total visits to be processed - %s (%s batches).</info>';
            while ($this->processTracking($this->calculateBatches($totalEvents, $message))) {
                if ($this->checkMaxExecutionTime()) {
                    return;
                }
            }
        }
    }

    private function recheckPreviousVisitIdentifiers(): void
    {
        $this->logger?->info('Recheck previous visit identifiers...');
        $totalVisits = $this->getTrackingVisitsCount();
        if ($totalVisits) {
            $this->processedBatches = 0;
            $message = '<info>Total previous visit identifiers to be processed - %s (%s batches).</info>';
            while ($this->processTrackingVisits($this->calculateBatches($totalVisits, $message))) {
                if ($this->checkMaxExecutionTime()) {
                    return;
                }
            }
        }
    }

    private function recheckPreviousVisitEvents(): void
    {
        $this->logger?->info('Recheck previous visit events...');
        $totalVisitEvents = $this->getTrackingVisitEventEventsCount();
        if ($totalVisitEvents) {
            $this->processedBatches = 0;
            $this->skipList = [];
            $message = '<info>Total previous visit events to be processed - %s (%s batches).</info>';
            while ($this->processTrackingVisitEvents($this->calculateBatches($totalVisitEvents, $message))) {
                if ($this->checkMaxExecutionTime()) {
                    return;
                }
            }
        }
    }

    /**
     * @param TrackingVisit[] $trackingVisits
     */
    protected function updateVisits(array $trackingVisits): void
    {
        foreach ($trackingVisits as $trackingVisit) {
            $message = 'Process visit id: %s, visitorUid: %s';
            $this->logger->info(sprintf($message, $trackingVisit->getId(), $trackingVisit->getVisitorUid()));
            $identifier = $trackingVisit->getIdentifierTarget();
            if ($identifier) {
                /** @var TrackingVisitRepository $trackingVisitRepository */
                $trackingVisitRepository = $this->getEntityManager()->getRepository(TrackingVisit::class);
                $trackingVisits = $trackingVisitRepository->getByTrackingWebsite(
                    $trackingVisit->getVisitorUid(),
                    $trackingVisit->getFirstActionTime(),
                    $trackingVisit->getTrackingWebsite()
                );

                if (!empty($trackingVisits)) {
                    array_walk($trackingVisits, fn ($value) => $value['id']);
                    /** @var TrackingVisitEventRepository $trackingVisitEventRepository */
                    $trackingVisitEventRepository = $this->getEntityManager()->getRepository(TrackingVisitEvent::class);
                    $trackingVisitEventRepository->updateIdentifier($identifier, $trackingVisits);
                }

                $trackingVisitRepository->updateIdentifier(
                    $identifier,
                    $trackingVisit->getVisitorUid(),
                    $trackingVisit->getFirstActionTime(),
                    $trackingVisit->getTrackingWebsite()
                );
            }
        }

        $this->deviceDetector->clearInstances();
    }

    private function getTrackingEventsCount(): int
    {
        /** @var TrackingEventRepository $trackingEventRepository */
        $trackingEventRepository = $this->getEntityManager()->getRepository(TrackingEvent::class);

        return $trackingEventRepository->getNotParsedTrackingEventsCount();
    }

    private function getTrackingVisitsCount(): int
    {
        /** @var TrackingVisitRepository $trackingVisitRepository */
        $trackingVisitRepository = $this->getEntityManager()->getRepository(TrackingVisit::class);
        $queryBuilder = $trackingVisitRepository->createNotDetectedTrackingVisitCountQueryBuilder(
            $this->getMaxRetriesCount()
        );

        return $this->applySkipList($queryBuilder)->getQuery()->getSingleScalarResult();
    }

    private function getTrackingVisitEventEventsCount(): int
    {
        /** @var TrackingVisitEventRepository $trackingVisitEventRepository */
        $trackingVisitEventRepository =  $this->getEntityManager()->getRepository(TrackingVisitEvent::class);
        $queryBuilder = $trackingVisitEventRepository->createTrackingVisitEventEntityCountQueryBuilder(
            $this->getMaxRetriesCount()
        );

        return $this->applySkipList($queryBuilder)->getQuery()->getSingleScalarResult();
    }

    public function hasTrackingEventsToProcess(): bool
    {
        return $this->getTrackingEventsCount() > 0;
    }

    private function processTracking(float $totalBatches): bool
    {
        /** @var TrackingEventRepository $trackingEventRepository */
        $trackingEventRepository = $this->getEntityManager()->getRepository(TrackingEvent::class);
        $trackingEvents = $trackingEventRepository->getNotParsedTrackingEvents(true, $this->getBatchSize());
        if ($trackingEvents) {
            try {
                $this->processTrackingEvents($trackingEvents);
            } catch (\Exception $e) {
                $this->skipTrackingEvents($trackingEvents);
                $this->logInvalidBatch(++$this->processedBatches, $totalBatches, $e->getMessage());

                return true;
            }

            $this->logBatch(++$this->processedBatches, $totalBatches);

            return true;
        }

        return false;
    }

    private function processTrackingVisits(float $totalBatches): bool
    {
        /** @var TrackingVisitRepository $trackingVisitRepository */
        $trackingVisitRepository = $this->getEntityManager()->getRepository(TrackingVisit::class);
        $queryBuilder = $trackingVisitRepository
            ->createNotDetectedTrackingVisitQueryBuilder($this->getMaxRetriesCount(), true, $this->getBatchSize());

        $trackingVisits = $this->applySkipList($queryBuilder)->getQuery()->getResult();
        if ($trackingVisits) {
            try {
                $this->processTrackingVisitsIdentifier($trackingVisits);
            } catch (\Exception $e) {
                $this->skipTrackingVisits($trackingVisits);
                $this->logInvalidBatch(++$this->processedBatches, $totalBatches, $e->getMessage());

                return true;
            }

            $this->logBatch(++$this->processedBatches, $totalBatches);

            return true;
        }

        return false;
    }

    private function processTrackingVisitEvents(float $totalBatches): bool
    {
        /** @var TrackingVisitEventRepository $trackingVisitEventRepository */
        $trackingVisitEventRepository =  $this->getEntityManager()->getRepository(TrackingVisitEvent::class);
        $queryBuilder = $trackingVisitEventRepository
            ->createTrackingVisitEventEntityQueryBuilder($this->getMaxRetriesCount())
            ->setMaxResults($this->getBatchSize());

        $trackingVisitEvents = $this->applySkipList($queryBuilder)->getQuery()->getResult();
        if ($trackingVisitEvents) {
            try {
                $this->processTrackingVisitEventsAssociation($trackingVisitEvents);
            } catch (\Exception $e) {
                $this->skipTrackingVisitEvents($trackingVisitEvents);
                $this->logInvalidBatch(++$this->processedBatches, $totalBatches, $e->getMessage());

                return true;
            }

            $this->logBatch(++$this->processedBatches, $totalBatches);

            return true;
        }

        return false;
    }

    /**
     * @param TrackingVisitEvent[] $trackingVisitEvents
     *
     * @return void
     */
    private function processTrackingVisitEventsAssociation(array $trackingVisitEvents): void
    {
        $entityManager = $this->getEntityManager();
        foreach ($trackingVisitEvents as $trackingVisitEvent) {
            $trackingVisitEvent->setParsingCount($trackingVisitEvent->getParsingCount() + 1);
            $this->skipList[] = $trackingVisitEvent->getId();
            $targets = $this->trackingIdentification->processEvent($trackingVisitEvent);
            if (!empty($targets)) {
                foreach ($targets as $target) {
                    $trackingVisitEvent->addAssociationTarget($target);
                }
            }

            $entityManager->persist($trackingVisitEvent);
        }

        $entityManager->flush();
        $entityManager->clear();
    }

    /**
     * @param TrackingVisit[] $trackingVisits
     *
     * @return void
     */
    private function processTrackingVisitsIdentifier(array $trackingVisits): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($trackingVisits as $trackingVisit) {
            $idObj = $this->trackingIdentification->identify($trackingVisit);
            if ($idObj && $idObj['targetObject']) {
                $trackingVisit->setIdentifierTarget($idObj['targetObject']);
                $trackingVisit->setIdentifierDetected(true);
                $this->logger?->info(sprintf('-- <comment>parsed UID "%s"</comment>', $idObj['parsedUID']));
            } else {
                $trackingVisit->setParsingCount($trackingVisit->getParsingCount() + 1);
                $this->skipList[] = $trackingVisit->getId();
            }

            $entityManager->persist($trackingVisit);
        }

        $entityManager->flush();
        $this->updateVisits($trackingVisits);
        $entityManager->clear();
    }

    /**
     * @param TrackingEvent[] $trackingEvents
     *
     * @return void
     */
    private function processTrackingEvents(array $trackingEvents): void
    {
        $entityManager = $this->getEntityManager();
        foreach ($trackingEvents as $trackingEvent) {
            $this->logger?->info(sprintf('Processing event - %s', $trackingEvent->getId()));
            $decodedData = json_decode($trackingEvent->getEventData()->getData(), true);

            $trackingEvent->setParsed(true);
            $trackingVisit = $this->generateTrackingVisit($trackingEvent, $decodedData);
            $trackingVisitEvent = $this->generateTrackingVisitEvent($trackingVisit, $trackingEvent);

            $entityManager->persist($trackingEvent);
            $entityManager->persist($trackingVisitEvent);
            $entityManager->persist($trackingVisit);
        }

        $entityManager->flush();

        $this->updateVisits($this->collectedVisits);
        $this->collectedVisits = [];
        $this->eventDictionary = [];


        $this->logMemoryUsage();
    }

    /**
     * @param TrackingEvent[] $trackingEvents
     *
     * @return void
     */
    private function skipTrackingEvents(array $trackingEvents): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($trackingEvents as $trackingEvent) {
            $trackingEvent = $this->refreshDetachedEvent($entityManager, $trackingEvent);
            $trackingEvent->setCode(TrackingEvent::INVALID_CODE);
            $trackingEvent->setParsed(false);
            $entityManager->persist($trackingEvent);
        }

        $entityManager->flush();
    }

    /**
     * @param TrackingVisit[] $trackingEvents
     *
     * @return void
     */
    private function skipTrackingVisits(array $trackingVisits): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($trackingVisits as $trackingVisit) {
            $trackingVisit = $this->refreshDetachedEvent($entityManager, $trackingVisit);
            $trackingVisit->setCode(TrackingVisit::INVALID_CODE);
            $entityManager->persist($trackingVisit);
        }

        $entityManager->flush();
    }

    /**
     * @param TrackingVisitEvent[] $trackingVisitEvents
     *
     * @return void
     */
    private function skipTrackingVisitEvents(array $trackingVisitEvents): void
    {
        $entityManager = $this->getEntityManager();

        foreach ($trackingVisitEvents as $trackingVisitEvent) {
            $trackingVisitEvent = $this->refreshDetachedEvent($entityManager, $trackingVisitEvent);
            $trackingVisitEvent->setCode(TrackingVisitEvent::INVALID_CODE);
            $entityManager->persist($trackingVisitEvent);
        }

        $entityManager->flush();
    }

    private function applySkipList(QueryBuilder $queryBuilder): QueryBuilder
    {
        if (count($this->skipList)) {
            $queryBuilder->andWhere('entity.id not in (:skipList)');
            $queryBuilder->setParameter('skipList', $this->skipList);
        }

        return $queryBuilder;
    }

    private function generateTrackingVisit(TrackingEvent $trackingEvent, array $decodedData): TrackingVisit
    {
        $visitorUid = $decodedData['_id'];
        $userIdentifier = $trackingEvent->getUserIdentifier();
        $hash = md5($visitorUid . $userIdentifier);

        // try to find existing visit
        if (!empty($this->collectedVisits) && array_key_exists($hash, $this->collectedVisits)) {
            $trackingVisit = $this->collectedVisits[$hash];
        } else {
            $trackingVisit = $this
                ->getEntityManager()
                ->getRepository(TrackingVisit::class)
                ->findOneBy(
                    [
                        'visitorUid' => $visitorUid,
                        'userIdentifier' => $trackingEvent->getUserIdentifier(),
                        'trackingWebsite' => $trackingEvent->getWebsite()
                    ]
                );
        }

        if (!$trackingVisit) {
            $trackingVisit = $this->createTrackingVisit($trackingEvent, $visitorUid, $decodedData);
            $this->collectedVisits[$hash] = $trackingVisit;
        } else {
            if ($trackingVisit->getFirstActionTime() > $trackingEvent->getCreatedAt()) {
                $trackingVisit->setFirstActionTime($trackingEvent->getCreatedAt());
            }
            if ($trackingVisit->getLastActionTime() < $trackingEvent->getCreatedAt()) {
                $trackingVisit->setLastActionTime($trackingEvent->getCreatedAt());
            }
        }

        return $trackingVisit;
    }

    private function generateTrackingVisitEvent(
        TrackingVisit $trackingVisit,
        TrackingEvent $trackingEvent
    ): TrackingVisitEvent {
        $trackingVisitEvent = new TrackingVisitEvent();
        $trackingVisitEvent->setParsingCount(0);
        $trackingVisitEvent->setEvent($this->getTrackingEventDictionary($trackingEvent));
        $trackingVisitEvent->setVisit($trackingVisit);
        $trackingVisitEvent->setWebEvent($trackingEvent);
        $trackingVisitEvent->setWebsite($trackingEvent->getWebsite());

        /** @var Campaign[] $targets */
        $targets = $this->trackingIdentification->processEvent($trackingVisitEvent);
        foreach ($targets as $target) {
            $trackingVisitEvent->addAssociationTarget($target);
        }

        return $trackingVisitEvent;
    }

    private function createTrackingVisit(
        TrackingEvent $trackingEvent,
        string $visitorUid,
        array $decodedData
    ): TrackingVisit {
        $trackingVisit = new TrackingVisit();
        $trackingVisit->setParsedUID(0);
        $trackingVisit->setParsingCount(0);
        $trackingVisit->setUserIdentifier($trackingEvent->getUserIdentifier());
        $trackingVisit->setVisitorUid($visitorUid);
        $trackingVisit->setFirstActionTime($trackingEvent->getCreatedAt());
        $trackingVisit->setLastActionTime($trackingEvent->getCreatedAt());
        $trackingVisit->setTrackingWebsite($trackingEvent->getWebsite());
        $trackingVisit->setIdentifierDetected(false);

        if (!empty($decodedData['cip'])) {
            $trackingVisit->setIp($decodedData['cip']);
        }

        if (!empty($decodedData['ua'])) {
            $this->setTrackingVisitUserAgent($trackingVisit, $decodedData['ua']);
        }

        $this->setTrackingVisitIdentifier($trackingVisit);

        $violations = $this->validator->validate($trackingVisit);
        if ($violations->count()) {
            throw new ValidatorException($violations);
        }

        return $trackingVisit;
    }

    private function setTrackingVisitIdentifier(TrackingVisit $trackingVisit)
    {
        /**
         * try to identify visit
         */
        $idObj = $this->trackingIdentification->identify($trackingVisit);
        if ($idObj) {
            /**
             * if identification was successful we should:
             *  - assign visit to target
             *  - assign all previous visits to same identified object(s).
             */
            $this->logger?->info('-- <comment>parsed UID "' . $idObj['parsedUID'] . '"</comment>');
            if ($idObj['parsedUID'] !== null) {
                $trackingVisit->setParsedUID($idObj['parsedUID']);
                if ($idObj['targetObject']) {
                    $trackingVisit->setIdentifierTarget($idObj['targetObject']);
                    $trackingVisit->setIdentifierDetected(true);
                }
            }
        }
    }

    private function setTrackingVisitUserAgent(TrackingVisit $trackingVisit, string $ua): void
    {
        $device = $this->deviceDetector->getInstance($ua);
        $os = $device->getOs();
        if (is_array($os)) {
            $trackingVisit->setOs($os['name'] ?? null);
            $trackingVisit->setOsVersion($os['version'] ?? null);
        }

        $client = $device->getClient();
        if (is_array($client)) {
            $trackingVisit->setClient($client['name'] ?? null);
            $trackingVisit->setClientType($client['type'] ?? null);
            $trackingVisit->setClientVersion($client['version'] ?? null);
        }

        $trackingVisit->setDesktop($device->isDesktop());
        $trackingVisit->setMobile($device->isMobile());
        $trackingVisit->setBot($device->isBot());
    }

    private function getTrackingEventDictionary(TrackingEvent $event): TrackingEventDictionary
    {
        $eventWebsite = $event->getWebsite();
        if ($eventWebsite && isset($this->eventDictionary[$eventWebsite->getId()][$event->getName()])) {
            $eventType = $this->eventDictionary[$eventWebsite->getId()][$event->getName()];
        } else {
            $eventType = $this
                ->getEntityManager()
                ->getRepository('OroTrackingBundle:TrackingEventDictionary')
                ->findOneBy(['name' => $event->getName(), 'website' => $eventWebsite]);
        }

        if (!$eventType) {
            $entityManager = $this->getEntityManager();
            $eventType = new TrackingEventDictionary();
            $eventType->setName($event->getName());
            $eventType->setWebsite($eventWebsite);
            $this->eventDictionary[$eventWebsite?->getId() ?? null][$event->getName()] = $eventType;
            $entityManager->persist($eventType);
        }

        return $eventType;
    }
}
