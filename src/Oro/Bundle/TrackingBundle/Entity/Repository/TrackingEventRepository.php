<?php

namespace Oro\Bundle\TrackingBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\TrackingBundle\Entity\TrackingEvent;

/**
 * Doctrine repository for TrackingEvent entity
 */
class TrackingEventRepository extends EntityRepository
{
    public function createNotParsedTrackingEventsQueryBuilder(
        bool $sortById = false,
        int $maxResults = null
    ): QueryBuilder {
        $queryBuilder = $this
            ->getEntityManager()
            ->getRepository(TrackingEvent::class)
            ->createQueryBuilder('entity')
            ->andWhere('entity.parsed = false')
            ->andWhere('entity.code IS NULL')
            ->innerJoin('entity.eventData', 'eventData');

        if ($sortById) {
            $queryBuilder->orderBy('entity.id', 'ASC');
        }

        if ($maxResults) {
            $queryBuilder->setMaxResults($maxResults);
        }

        return $queryBuilder;
    }

    public function getNotParsedTrackingEvents(bool $sortById = false, int $maxResults = null): array
    {
        return $this
            ->createNotParsedTrackingEventsQueryBuilder($sortById, $maxResults)
            ->getQuery()
            ->getResult();
    }

    public function getNotParsedTrackingEventsCount(): int
    {
        return $this
            ->createNotParsedTrackingEventsQueryBuilder()
            ->select('COUNT (entity.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
