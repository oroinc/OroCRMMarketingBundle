<?php

namespace Oro\Bundle\TrackingBundle\Processor;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Describes basic methods of tracking data processing.
 */
abstract class AbstractTrackingProcessor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const BATCH_SIZE = 100; # Batch size for tracking events.
    private const MAX_RETRIES = 5; #  Max retries to identify tracking visit.

    private ?\DateTime $startTime;
    private \DateInterval|bool $maxExecTimeout;
    private int $maxExecTime = 5; # Default max execution time (in minutes).

    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
        $this->maxExecTimeout = $this->maxExecTime > 0 ? new \DateInterval('PT' . $this->maxExecTime . 'M') : false;
        $this->startTime = $this->getCurrentUtcDateTime();
    }

    public function setMaxExecutionTime(?int $minutes): void
    {
        if ($minutes) {
            $this->maxExecTime = $minutes;
            $this->maxExecTimeout = $minutes > 0 ? new \DateInterval('PT' . $minutes . 'M') : false;
        }
    }

    protected function checkMaxExecutionTime(): bool
    {
        if ($this->maxExecTimeout !== false) {
            $date = $this->getCurrentUtcDateTime();
            if ($date->sub($this->maxExecTimeout) >= $this->startTime) {
                $this->logger?->info('<comment>Exit because allocated time frame elapsed.</comment>');

                return true;
            }
        }

        return false;
    }

    protected function logBatch(int $processed, float $total): void
    {
        $message = 'Batch #%s of %s processed at <info>%s</info>.';
        $this->logger?->info(sprintf($message, number_format($processed), number_format($total), date('Y-m-d H:i:s')));
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

    protected function calculateBatches(int $count, string $message): int
    {
        $totalBatches = ceil($count / $this->getBatchSize());
        $this->logger?->info(sprintf($message, number_format($count), number_format($totalBatches)));

        return $totalBatches;
    }

    protected function getMaxRetriesCount(): int
    {
        return self::MAX_RETRIES;
    }

    protected function getBatchSize(): int
    {
        return self::BATCH_SIZE;
    }

    private function getCurrentUtcDateTime(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }

    protected function getEntityManager(): EntityManager
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManager();
        if (!$entityManager->isOpen()) {
            $this->registry->resetManager();
            $entityManager = $this->registry->getManager();
        }

        return $entityManager;
    }

    protected function refreshDetachedEvent(EntityManager $entityManager, object $event): object
    {
        $event = $entityManager->merge($event);
        $entityManager->refresh($event);

        return $event;
    }

    /**
     * To avoid memory leaks, we turn off doctrine logger.
     */
    protected function turnOffDoctrineLogger(): void
    {
        $this->getEntityManager()->getConnection()->getConfiguration()->setSQLLogger();
    }
}
