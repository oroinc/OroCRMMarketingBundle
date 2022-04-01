<?php

namespace Oro\Bundle\TrackingBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;

/**
 * Doctrine repository for TrackingVisit entity
 */
class TrackingVisitRepository extends EntityRepository
{
    public function createNotDetectedTrackingVisitQueryBuilder(
        int $maxRetriesCount,
        bool $sortByActionTime = false,
        int $maxResults = null
    ): QueryBuilder {
        $queryBuilder = $this
            ->createQueryBuilder('entity')
            ->where('entity.identifierDetected = false')
            ->andWhere('entity.parsedUID > 0')
            ->andWhere('entity.parsingCount < :maxRetries')
            ->andWhere('entity.code IS NULL')
            ->setParameter('maxRetries', $maxRetriesCount);

        if ($sortByActionTime) {
            $queryBuilder->orderBy('entity.firstActionTime', 'ASC');
        }

        if ($maxResults) {
            $queryBuilder->setMaxResults($maxResults);
        }

        return $queryBuilder;
    }

    public function createNotDetectedTrackingVisitCountQueryBuilder(int $maxRetriesCount): QueryBuilder
    {
        return $this
            ->createNotDetectedTrackingVisitQueryBuilder($maxRetriesCount)
            ->select('COUNT (entity.id)');
    }

    public function getByTrackingWebsite(
        string $visitorUid,
        \DateTime $firstActionTime,
        ?TrackingWebsite $website
    ): array {
        $qb = $this
            ->createQueryBuilder('entity')
            ->select('entity.id')
            ->where('entity.visitorUid = :visitorUid')
            ->andWhere('entity.firstActionTime < :maxDate')
            ->andWhere('entity.identifierDetected = false')
            ->andWhere('entity.parsedUID = 0')
            ->andWhere('entity.trackingWebsite  = :website')
            ->setParameter('visitorUid', $visitorUid)
            ->setParameter('maxDate', $firstActionTime, Types::DATETIME_MUTABLE)
            ->setParameter('website', $website);

        return $qb->getQuery()->getArrayResult();
    }

    public function updateIdentifier(
        ?object $identifier,
        string $visitorUid,
        \DateTime $firstActionTime,
        ?TrackingWebsite $website
    ): void {
        $associationName = ExtendHelper::buildAssociationName(
            ClassUtils::getClass($identifier),
            IdentifierEventExtension::ASSOCIATION_KIND
        );

        $this->createQueryBuilder('entity')
            ->update()
            ->set(sprintf('entity.%s', $associationName), ':identifier')
            ->set('entity.identifierDetected', ':detected')
            ->where('entity.visitorUid = :visitorUid')
            ->andWhere('entity.firstActionTime < :maxDate')
            ->andWhere('entity.identifierDetected = false')
            ->andWhere('entity.parsedUID = 0')
            ->andWhere('entity.trackingWebsite  = :website')
            ->setParameter('visitorUid', $visitorUid)
            ->setParameter('maxDate', $firstActionTime, Types::DATETIME_MUTABLE)
            ->setParameter('website', $website)
            ->setParameter('identifier', $identifier)
            ->setParameter('detected', true)
            ->getQuery()
            ->execute();
    }
}
