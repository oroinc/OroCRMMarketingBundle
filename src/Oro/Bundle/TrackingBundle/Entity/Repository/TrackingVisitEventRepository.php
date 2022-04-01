<?php

namespace Oro\Bundle\TrackingBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;

/**
 * Doctrine repository for TrackingVisitEvent entity
 */
class TrackingVisitEventRepository extends EntityRepository
{
    public function createTrackingVisitEventEntityQueryBuilder(
        int $maxRetriesCount,
        int $maxResults = null
    ): QueryBuilder {
        $queryBuilder = $this
            ->getEntityManager()
            ->getRepository(TrackingVisitEvent::class)
            ->createQueryBuilder('entity')
            ->andWhere('entity.parsingCount < :maxRetries')
            ->andWhere('entity.code IS NULL')
            ->setParameter('maxRetries', $maxRetriesCount);

        if ($maxResults) {
            $queryBuilder->setMaxResults($maxResults);
        }

        return $queryBuilder;
    }

    public function createTrackingVisitEventEntityCountQueryBuilder(int $maxRetriesCount): QueryBuilder
    {
        return $this
            ->createTrackingVisitEventEntityQueryBuilder($maxRetriesCount)
            ->select('COUNT (entity.id)');
    }

    public function updateIdentifier(?object $identifier, array $visitIds): void
    {
        $associationName = ExtendHelper::buildAssociationName(
            ClassUtils::getClass($identifier),
            VisitEventAssociationExtension::ASSOCIATION_KIND
        );

        $this
            ->createQueryBuilder('event')
            ->update()
            ->set(sprintf('event.%s', $associationName), ':identifier')
            ->where('event.visit in (:visitIds)')
            ->setParameter('visitIds', $visitIds)
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->execute();
    }
}
