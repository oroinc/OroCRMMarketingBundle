<?php

namespace Oro\Bundle\TrackingBundle\Processor;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;
use Oro\Bundle\TrackingBundle\Entity\TrackingEventDictionary;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Provider\TrackingEventIdentificationProvider;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processes (parses) tracking logs.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class TrackingProcessor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const TRACKING_EVENT_ENTITY = 'OroTrackingBundle:TrackingEvent';
    const TRACKING_VISIT_ENTITY = 'OroTrackingBundle:TrackingVisit';
    const TRACKING_VISIT_EVENT_ENTITY = 'OroTrackingBundle:TrackingVisitEvent';

    /** Batch size for tracking events */
    const BATCH_SIZE = 100;

    /** Max retries to identify tracking visit */
    const MAX_RETRIES = 5;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var array */
    protected $collectedVisits = [];

    /** @var array */
    protected $eventDictionary = [];

    /** @var TrackingEventIdentificationProvider */
    protected $trackingIdentification;

    /** @var int */
    protected $processedBatches = 0;

    /** @var array */
    protected $skipList = [];

    /** @var DeviceDetectorFactory */
    protected $deviceDetector;

    /** @var \DateTime start time */
    protected $startTime = null;

    /** @var \DateInterval|bool */
    protected $maxExecTimeout = false;

    /** Default max execution time (in minutes) */
    protected $maxExecTime = 5;

    /** @var ValidatorInterface */
    protected $validator;

    public function __construct(ManagerRegistry $doctrine, TrackingEventIdentificationProvider $trackingIdentification)
    {
        $this->doctrine               = $doctrine;
        $this->trackingIdentification = $trackingIdentification;
        $this->deviceDetector         = new DeviceDetectorFactory();

        $this->startTime      = $this->getCurrentUtcDateTime();
        $this->maxExecTimeout = $this->maxExecTime > 0
            ? new \DateInterval('PT' . $this->maxExecTime . 'M')
            : false;
    }

    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * @param integer $minutes
     */
    public function setMaxExecutionTime($minutes = null)
    {
        if ($minutes !== null) {
            $this->maxExecTime    = $minutes;
            $this->maxExecTimeout = $minutes > 0 ? new \DateInterval('PT' . $minutes . 'M') : false;
        }
    }

    /**
     * @return bool
     */
    public function hasEntitiesToProcess()
    {
        return $this->getEventsCount() > 0;
    }

    /**
     * Process tracking data
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process()
    {
        /** To avoid memory leaks, we turn off doctrine logger */
        $this->getEntityManager()->getConnection()->getConfiguration()->setSQLLogger(null);

        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        $this->checkNewVisits();
        $this->recheckPreviousVisitIdentifiers();
        $this->recheckPreviousVisitEvents();

        $this->logger->info('<info>Done</info>');
    }

    private function checkNewVisits(): void
    {
        $this->logger->info('Check new visits...');
        $totalEvents = $this->getEventsCount();
        if ($totalEvents > 0) {
            $totalBatches = number_format(ceil($totalEvents / $this->getBatchSize()));
            $this->logger->info(
                sprintf(
                    '<info>Total visits to be processed - %s (%s batches).</info>',
                    number_format($totalEvents),
                    $totalBatches
                )
            );
            while ($this->processTracking($totalBatches)) {
                if ($this->checkMaxExecutionTime()) {
                    return;
                }
            }
        }
    }

    private function recheckPreviousVisitIdentifiers(): void
    {
        $this->logger->info('Recheck previous visit identifiers...');
        $totalEvents = $this->getIdentifyPrevVisitsCount();
        if ($totalEvents > 0) {
            $totalBatches           = number_format(ceil($totalEvents / $this->getBatchSize()));
            $this->processedBatches = 0;
            $this->logger->info(
                sprintf(
                    '<info>Total previous visit identifiers to be processed - %s (%s batches).</info>',
                    number_format($totalEvents),
                    $totalBatches
                )
            );
            while ($this->processIdentifyPrevVisits($totalBatches)) {
                if ($this->checkMaxExecutionTime()) {
                    return;
                }
            }
        }
    }

    private function recheckPreviousVisitEvents(): void
    {
        $this->logger->info('Recheck previous visit events...');
        $totalEvents = $this->getIdentifyPrevVisitEventsCount();
        if ($totalEvents > 0) {
            $totalBatches           = number_format(ceil($totalEvents / $this->getBatchSize()));
            $this->processedBatches = 0;
            $this->logger->info(
                sprintf(
                    '<info>Total previous visit events to be processed - %s (%s batches).</info>',
                    number_format($totalEvents),
                    $totalBatches
                )
            );
            $this->skipList = [];
            while ($this->processRecheckPreviousVisitEvents($totalBatches)) {
                if ($this->checkMaxExecutionTime()) {
                    return;
                }
            }
        }
    }

    /**
     * @param integer $processed
     * @param string  $total
     */
    protected function logBatch($processed, $total)
    {
        $this->logger->info(
            sprintf(
                'Batch #%s of %s processed at <info>%s</info>.',
                number_format($processed),
                $total,
                date('Y-m-d H:i:s')
            )
        );
    }

    protected function logInvalidBatch(int $processed, float $total, string $additionalMessage): void
    {
        $message = 'Batch #%s of %s could not be processed. Please fix tracking data and re-parse the data again.';
        $this->logger?->info(sprintf($message, number_format($processed), number_format($total), date('Y-m-d H:i:s')));
        $this->logger?->info($additionalMessage);
    }

    protected function logMemoryUsage(): void
    {
        $message = '<comment>Memory usage (currently) %dMB/ (max) %dMB</comment>';
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024);
        $memoryPeakUsage = memory_get_peak_usage(true) / 1024 / 1024;
        $this->logger?->info(sprintf($message, $memoryUsage, $memoryPeakUsage));
    }

    /**
     * Checks of process max execution time
     *
     * @return bool
     */
    protected function checkMaxExecutionTime()
    {
        if ($this->maxExecTimeout !== false) {
            $date = $this->getCurrentUtcDateTime();
            if ($date->sub($this->maxExecTimeout) >= $this->startTime) {
                $this->logger->info('<comment>Exit because allocated time frame elapsed.</comment>');

                return true;
            }
        }

        return false;
    }

    /**
     * Returns count of web events to be processed.
     *
     * @return integer
     */
    protected function getEventsCount()
    {
        $queryBuilder = $this->createNotParsedEntityQueryBuilder()
            ->select('COUNT (entity.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns count of not identified web visits to be processed.
     *
     * @return integer
     */
    protected function getIdentifyPrevVisitsCount()
    {
        $em           = $this->getEntityManager();
        $queryBuilder = $em
            ->getRepository(self::TRACKING_VISIT_ENTITY)
            ->createQueryBuilder('entity');
        $queryBuilder
            ->select('COUNT (entity.id)')
            ->where('entity.identifierDetected = false')
            ->andWhere('entity.parsedUID > 0')
            ->andWhere('entity.code IS NULL')
            ->andWhere('entity.parsingCount < :maxRetries')
            ->setParameter('maxRetries', $this->getMaxRetriesCount());

        $this->applySkipList($queryBuilder);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns count of tracking visit events to be processed.
     *
     * @return integer
     */
    protected function getIdentifyPrevVisitEventsCount()
    {
        $em           = $this->getEntityManager();
        $queryBuilder = $em
            ->getRepository(self::TRACKING_VISIT_EVENT_ENTITY)
            ->createQueryBuilder('entity');
        $queryBuilder
            ->select('COUNT (entity.id)')
            ->andWhere('entity.parsingCount < :maxRetries')
            ->andWhere('entity.code IS NULL')
            ->setParameter('maxRetries', $this->getMaxRetriesCount());

        $this->applySkipList($queryBuilder);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @deprecated to be removed in 5.1, use `processRecheckPreviousVisitEvents()` instead
     *
     * @return bool
     *
     * Process previous visit events
     */
    protected function identifyPrevVisitEvents()
    {
        $totalEvents = $this->getIdentifyPrevVisitEventsCount();
        $totalBatches = number_format(ceil($totalEvents / $this->getBatchSize()));

        return $this->processRecheckPreviousVisitEvents($totalBatches);
    }

    protected function processRecheckPreviousVisitEvents(int $totalBatches): bool
    {
        $em = $this->getEntityManager();
        $queryBuilder = $em
            ->getRepository(self::TRACKING_VISIT_EVENT_ENTITY)
            ->createQueryBuilder('entity');
        $queryBuilder
            ->select('entity')
            ->andWhere('entity.parsingCount < :maxRetries')
            ->andWhere('entity.code IS NULL')
            ->setParameter('maxRetries', $this->getMaxRetriesCount())
            ->setMaxResults($this->getBatchSize());

        $this->applySkipList($queryBuilder);

        /** @var TrackingVisitEvent[] $entities */
        $entities = $queryBuilder->getQuery()->getResult();
        if ($entities) {
            try {
                $this->processIdentifyPrevVisitEvents($entities);
            } catch (\Exception $e) {
                $this->skipTrackingVisitEvents($entities);
                $this->logInvalidBatch(++$this->processedBatches, $totalBatches, $e->getMessage());

                return true;
            }

            return true;
        }

        return false;
    }

    private function processIdentifyPrevVisitEvents(array $entities): void
    {
        $em = $this->getEntityManager();
        foreach ($entities as $visitEvent) {
            $visitEvent->setParsingCount($visitEvent->getParsingCount() + 1);
            $this->skipList[] = $visitEvent->getId();
            $targets = $this->trackingIdentification->processEvent($visitEvent);
            if (!empty($targets)) {
                foreach ($targets as $target) {
                    $visitEvent->addAssociationTarget($target);
                }
            }

            $em->persist($visitEvent);
        }

        $em->flush();
        $em->clear();
    }

    /**
     * @deprecated to be removed in 5.1, use `processIdentifyPrevVisits()` instead
     *
     * Identify previous visits in case than we haven't data to identify visit previously
     */
    protected function identifyPrevVisits()
    {
        $totalEvents = $this->getIdentifyPrevVisitsCount();
        $totalBatches = number_format(ceil($totalEvents / $this->getBatchSize()));

        return $this->processIdentifyPrevVisits($totalBatches);
    }

    private function processIdentifyPrevVisits(int $totalBatches): bool
    {
        $em = $this->getEntityManager();
        $queryBuilder = $em
            ->getRepository(self::TRACKING_VISIT_ENTITY)
            ->createQueryBuilder('entity');
        $queryBuilder
            ->select('entity')
            ->where('entity.identifierDetected = false')
            ->andWhere('entity.parsedUID > 0')
            ->andWhere('entity.code IS NULL')
            ->andWhere('entity.parsingCount < :maxRetries')
            ->orderBy('entity.firstActionTime', 'ASC')
            ->setParameter('maxRetries', $this->getMaxRetriesCount())
            ->setMaxResults($this->getBatchSize());

        $this->applySkipList($queryBuilder);

        $entities = $queryBuilder->getQuery()->getResult();
        if ($entities) {
            try {
                $this->processTrackingVisitsIdentifier($entities);
            } catch (\Exception $e) {
                $this->skipTrackingVisits($entities);
                $this->logInvalidBatch(++$this->processedBatches, $totalBatches, $e->getMessage());

                return true;
            }

            $this->logBatch(++$this->processedBatches, $totalBatches);

            return true;
        }

        return false;
    }

    private function processTrackingVisitsIdentifier(array $entities): void
    {
        $em = $this->getEntityManager();
        /** @var TrackingVisit $visit */
        foreach ($entities as $visit) {
            $idObj = $this->trackingIdentification->identify($visit);
            if ($idObj && $idObj['targetObject']) {
                $visit->setIdentifierTarget($idObj['targetObject']);
                $visit->setIdentifierDetected(true);

                $this->logger->info('-- <comment>parsed UID "' . $idObj['parsedUID'] . '"</comment>');
            } else {
                $visit->setParsingCount($visit->getParsingCount() + 1);
                $this->skipList[] = $visit->getId();
            }

            $em->persist($visit);
            $this->collectedVisits[] = $visit;
        }

        $em->flush();

        $this->updateVisits($this->collectedVisits);

        $this->collectedVisits = [];
        $em->clear();
    }

    /**
     * Identify previous visits
     *
     * @param array $entities
     */
    protected function updateVisits($entities)
    {
        /** @var TrackingVisit $visit */
        foreach ($entities as $visit) {
            $this->logger->info(
                sprintf(
                    'Process visit id: %s, visitorUid: %s',
                    $visit->getId(),
                    $visit->getVisitorUid()
                )
            );

            $identifier = $visit->getIdentifierTarget();
            if ($identifier) {
                // update tracking event identifiers
                $associationName = ExtendHelper::buildAssociationName(
                    ClassUtils::getClass($identifier),
                    VisitEventAssociationExtension::ASSOCIATION_KIND
                );

                $qb = $this->getEntityManager()
                    ->createQueryBuilder();

                $subSelect = $qb->select('entity.id')
                    ->from(self::TRACKING_VISIT_ENTITY, 'entity')
                    ->where('entity.visitorUid = :visitorUid')
                    ->andWhere('entity.firstActionTime < :maxDate')
                    ->andWhere('entity.identifierDetected = false')
                    ->andWhere('entity.parsedUID = 0')
                    ->andWhere('entity.trackingWebsite  = :website')
                    ->setParameter('visitorUid', $visit->getVisitorUid())
                    ->setParameter('maxDate', $visit->getFirstActionTime(), Types::DATETIME_MUTABLE)
                    ->setParameter('website', $visit->getTrackingWebsite())
                    ->getQuery()
                    ->getArrayResult();
                if (!empty($subSelect)) {
                    array_walk(
                        $subSelect,
                        function (&$value) {
                            $value = $value['id'];
                        }
                    );

                    $this->getEntityManager()->createQueryBuilder()
                        ->update(self::TRACKING_VISIT_EVENT_ENTITY, 'event')
                        ->set('event.' . $associationName, ':identifier')
                        ->where('event.visit in (:visitIds)')
                        ->setParameter('visitIds', $subSelect)
                        ->setParameter('identifier', $identifier)
                        ->getQuery()
                        ->execute();
                }

                $associationName = ExtendHelper::buildAssociationName(
                    ClassUtils::getClass($identifier),
                    IdentifierEventExtension::ASSOCIATION_KIND
                );

                $this->getEntityManager()
                    ->createQueryBuilder()
                    ->update(self::TRACKING_VISIT_ENTITY, 'entity')
                    ->set('entity.' . $associationName, ':identifier')
                    ->set('entity.identifierDetected', ':detected')
                    ->where('entity.visitorUid = :visitorUid')
                    ->andWhere('entity.firstActionTime < :maxDate')
                    ->andWhere('entity.identifierDetected = false')
                    ->andWhere('entity.parsedUID = 0')
                    ->andWhere('entity.trackingWebsite  = :website')
                    ->setParameter('visitorUid', $visit->getVisitorUid())
                    ->setParameter('maxDate', $visit->getFirstActionTime(), Types::DATETIME_MUTABLE)
                    ->setParameter('website', $visit->getTrackingWebsite())
                    ->setParameter('identifier', $identifier)
                    ->setParameter('detected', true)
                    ->getQuery()
                    ->execute();
            }
        }

        $this->deviceDetector->clearInstances();
    }

    /**
     * @deprecated to be removed in 5.1, use `processTracking()` instead
     * Collect new tracking visits with tracking visit events
     */
    protected function processVisits()
    {
        $totalEvents = $this->getEventsCount();
        $totalBatches = number_format(ceil($totalEvents / $this->getBatchSize()));

        return $this->processTracking($totalBatches);
    }

    private function processTracking(float $totalBatches): bool
    {
        $queryBuilder = $this->createNotParsedEntityQueryBuilder()
            ->orderBy('entity.id', 'ASC')
            ->setMaxResults($this->getBatchSize());

        $entities = $queryBuilder->getQuery()->getResult();

        if ($entities) {
            try {
                $this->processTrackingVisits($entities);
            } catch (\Exception $e) {
                $this->skipTrackingEvents($entities);
                $this->logInvalidBatch(++$this->processedBatches, $totalBatches, $e->getMessage());

                return true;
            }

            $this->logBatch(++$this->processedBatches, $totalBatches);

            return true;
        }

        return false;
    }

    /**
     * @return QueryBuilder
     */
    protected function createNotParsedEntityQueryBuilder()
    {
        return $this->getEntityManager()
            ->getRepository(self::TRACKING_EVENT_ENTITY)
            ->createQueryBuilder('entity')
            ->andWhere('entity.parsed = false')
            ->andWhere('entity.code IS NULL')
            ->innerJoin('entity.eventData', 'eventData');
    }

    /**
     * @param array|TrackingEvent[] $entities
     */
    protected function processTrackingVisits($entities)
    {
        $em = $this->getEntityManager();

        /** @var  TrackingEvent $event */
        foreach ($entities as $event) {
            $this->logger->info('Processing event - ' . $event->getId());

            $trackingVisitEvent = new TrackingVisitEvent();
            $trackingVisitEvent->setParsingCount(0);
            $trackingVisitEvent->setEvent($this->getEventType($event));

            $eventData   = $event->getEventData();
            $decodedData = json_decode($eventData->getData(), true);

            $trackingVisit = $this->getTrackingVisit($event, $decodedData);
            $trackingVisitEvent->setVisit($trackingVisit);
            $trackingVisitEvent->setWebEvent($event);
            $trackingVisitEvent->setWebsite($event->getWebsite());

            $targets = $this->trackingIdentification->processEvent($trackingVisitEvent);
            if (!empty($targets)) {
                foreach ($targets as $target) {
                    $trackingVisitEvent->addAssociationTarget($target);
                }
            }

            $event->setParsed(true);

            $em->persist($event);
            $em->persist($trackingVisitEvent);
            $em->persist($trackingVisit);
        }

        $em->flush();

        $this->updateVisits($this->collectedVisits);

        $this->collectedVisits = [];
        $this->eventDictionary = [];
        $em->clear();

        $this->logMemoryUsage();
    }

    /**
     * @param TrackingEvent $trackingEvent
     * @param array         $decodedData
     *
     * @return TrackingVisit
     */
    protected function getTrackingVisit(TrackingEvent $trackingEvent, $decodedData)
    {
        $visitorUid     = $decodedData['_id'];
        $userIdentifier = $trackingEvent->getUserIdentifier();

        $hash = md5($visitorUid . $userIdentifier);

        // try to find existing visit
        if (!empty($this->collectedVisits) && array_key_exists($hash, $this->collectedVisits)) {
            $visit = $this->collectedVisits[$hash];
        } else {
            $visit = $this->doctrine->getRepository(self::TRACKING_VISIT_ENTITY)->findOneBy(
                [
                    'visitorUid'      => $visitorUid,
                    'userIdentifier'  => $trackingEvent->getUserIdentifier(),
                    'trackingWebsite' => $trackingEvent->getWebsite()
                ]
            );
        }

        if (!$visit) {
            $visit = new TrackingVisit();
            $visit->setParsedUID(0);
            $visit->setParsingCount(0);
            $visit->setUserIdentifier($trackingEvent->getUserIdentifier());
            $visit->setVisitorUid($visitorUid);
            $visit->setFirstActionTime($trackingEvent->getCreatedAt());
            $visit->setLastActionTime($trackingEvent->getCreatedAt());
            $visit->setTrackingWebsite($trackingEvent->getWebsite());
            $visit->setIdentifierDetected(false);

            if (!empty($decodedData['cip'])) {
                $visit->setIp($decodedData['cip']);
            }

            if (!empty($decodedData['ua'])) {
                $this->processUserAgentString($visit, $decodedData['ua']);
            }

            $this->identifyTrackingVisit($visit);

            $violations = $this->validator->validate($visit);
            if ($violations->count()) {
                throw new ValidatorException($violations);
            }

            $this->collectedVisits[$hash] = $visit;
        } else {
            if ($visit->getFirstActionTime() > $trackingEvent->getCreatedAt()) {
                $visit->setFirstActionTime($trackingEvent->getCreatedAt());
            }
            if ($visit->getLastActionTime() < $trackingEvent->getCreatedAt()) {
                $visit->setLastActionTime($trackingEvent->getCreatedAt());
            }
        }

        return $visit;
    }

    /**
     * @param TrackingVisit $visit
     * @param string        $ua
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * because of ternary operators which in current case is clear enough to replace it with 'if' statement.
     */
    protected function processUserAgentString(TrackingVisit $visit, $ua)
    {
        $device = $this->deviceDetector->getInstance($ua);
        $os     = $device->getOs();
        if (is_array($os)) {
            $visit->setOs(isset($os['name']) ? $os['name'] : null);
            $visit->setOsVersion(isset($os['version']) ? $os['version'] : null);
        }

        $client = $device->getClient();
        if (is_array($client)) {
            $visit->setClient(isset($client['name']) ? $client['name'] : null);
            $visit->setClientType(isset($client['type']) ? $client['type'] : null);
            $visit->setClientVersion(isset($client['version']) ? $client['version'] : null);
        }

        $visit->setDesktop($device->isDesktop());
        $visit->setMobile($device->isMobile());
        $visit->setBot($device->isBot());
    }

    /**
     * Get Event dictionary for given tracking event
     *
     * @param TrackingEvent $event
     *
     * @return TrackingEventDictionary
     */
    protected function getEventType(TrackingEvent $event)
    {
        $eventWebsite = $event->getWebsite();
        if ($eventWebsite
            && isset(
                $this->eventDictionary[$eventWebsite->getId()],
                $this->eventDictionary[$eventWebsite->getId()][$event->getName()]
            )
        ) {
            $eventType = $this->eventDictionary[$eventWebsite->getId()][$event->getName()];
        } else {
            $eventType = $this->getEntityManager()
                ->getRepository('OroTrackingBundle:TrackingEventDictionary')
                ->findOneBy(
                    [
                        'name'    => $event->getName(),
                        'website' => $eventWebsite
                    ]
                );
        }

        if (!$eventType) {
            $eventType = new TrackingEventDictionary();
            $eventType->setName($event->getName());
            $eventType->setWebsite($eventWebsite);

            $this->getEntityManager()->persist($eventType);

            $this->eventDictionary[$eventWebsite ? $eventWebsite->getId() : null][$event->getName()] = $eventType;
        }

        return $eventType;
    }

    protected function identifyTrackingVisit(TrackingVisit $visit)
    {
        /**
         * try to identify visit
         */
        $idObj = $this->trackingIdentification->identify($visit);
        if ($idObj) {
            /**
             * if identification was successful we should:
             *  - assign visit to target
             *  - assign all previous visits to same identified object(s).
             */
            $this->logger->info('-- <comment>parsed UID "' . $idObj['parsedUID'] . '"</comment>');
            if ($idObj['parsedUID'] !== null) {
                $visit->setParsedUID($idObj['parsedUID']);
                if ($idObj['targetObject']) {
                    $visit->setIdentifierTarget($idObj['targetObject']);
                    $visit->setIdentifierDetected(true);
                }
            }
        }
    }

    /**
     * Returns default entity manager
     *
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();
        if (!$em->isOpen()) {
            $this->doctrine->resetManager();
            $em = $this->doctrine->getManager();
        }

        return $em;
    }

    /**
     * Get max retries to identify tracking visit
     *
     * @return int
     */
    protected function getMaxRetriesCount()
    {
        return self::MAX_RETRIES;
    }

    /**
     * Get batch size for tracking events
     *
     * @return int
     */
    protected function getBatchSize()
    {
        return self::BATCH_SIZE;
    }

    /**
     * Gets a DateTime object that is set to the current date and time in UTC.
     *
     * @return \DateTime
     */
    protected function getCurrentUtcDateTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Applies skipped items to query as filter
     */
    protected function applySkipList(QueryBuilder $queryBuilder)
    {
        if (count($this->skipList)) {
            $queryBuilder->andWhere('entity.id not in (:skipList)');
            $queryBuilder->setParameter('skipList', $this->skipList);
        }
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

    protected function refreshDetachedEvent(EntityManager $entityManager, object $event): object
    {
        $event = $entityManager->merge($event);
        $entityManager->refresh($event);

        return $event;
    }
}
