<?php

namespace Oro\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Doctrine repository for CampaignBundle Campaign entity.
 */
class CampaignRepository extends EntityRepository
{
    public function findOneByCode(string $code): ?Campaign
    {
        $qb = $this->createQueryBuilder('campaign')
            ->join(
                CampaignCodeHistory::class,
                'campaignCodeHistory',
                'WITH',
                'campaignCodeHistory.campaign = campaign'
            )
            ->where('campaignCodeHistory.code = :code')
            ->setParameter('code', $code);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getCodesHistory(Campaign $campaign, bool $excludeCurrent = true): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaignCodeHistory.code')
            ->from(CampaignCodeHistory::class, 'campaignCodeHistory')
            ->where('campaignCodeHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign->getId(), Types::INTEGER);
        if ($excludeCurrent) {
            $qb->andWhere('campaignCodeHistory.code != :code')
                ->setParameter('code', $campaign->getCode());
        }

        $result = $qb->getQuery()->getArrayResult();
        if ($result) {
            $result = array_column($result, 'code');
        }

        return $result;
    }

    public function getCampaignsLeads(AclHelper $aclHelper, int $recordsCount, ?array $dateRange = null): array
    {
        $qb = $this->createQueryBuilder('campaign');
        $qb
            ->select('campaign.name as label', 'COUNT(lead.id) as number', 'MAX(campaign.createdAt) as maxCreated')
            ->leftJoin(Lead::class, 'lead', 'WITH', 'lead.campaign = campaign')
            ->orderBy('maxCreated', 'DESC')
            ->groupBy('campaign.name')
            ->setMaxResults($recordsCount);
        if ($dateRange) {
            $qb->where($qb->expr()->between('lead.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateRange['start'], Types::DATETIME_MUTABLE)
                ->setParameter('dateTo', $dateRange['end'], Types::DATETIME_MUTABLE);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    public function getCampaignsLeadsQB($leadAlias): QueryBuilder
    {
        QueryBuilderUtil::checkIdentifier($leadAlias);

        return $this->createQueryBuilder('campaign')
            ->select(
                'campaign.name as label',
                sprintf('COUNT(%s.id) as number', $leadAlias),
                'MAX(campaign.createdAt) as maxCreated'
            )
            ->leftJoin(Lead::class, $leadAlias, 'WITH', sprintf('%s.campaign = campaign', $leadAlias))
            ->orderBy('maxCreated', 'DESC')
            ->groupBy('campaign.name');
    }

    public function getCampaignsOpportunities(AclHelper $aclHelper, int $recordsCount, ?array $dateRange = null): array
    {
        $qb = $this->createQueryBuilder('campaign')
            ->select('campaign.name as label', 'COUNT(opportunities.id) as number')
            ->join(Lead::class, 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', 'opportunities')
            ->orderBy('number', 'DESC')
            ->groupBy('campaign.name')
            ->setMaxResults($recordsCount);

        if ($dateRange) {
            $qb->where($qb->expr()->between('opportunities.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateFrom', $dateRange['start'], Types::DATETIME_MUTABLE)
                ->setParameter('dateTo', $dateRange['end'], Types::DATETIME_MUTABLE);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    public function getCampaignsOpportunitiesQB(string $opportunitiesAlias): QueryBuilder
    {
        QueryBuilderUtil::checkIdentifier($opportunitiesAlias);

        return $this->createQueryBuilder('campaign')
            ->select('campaign.name as label', sprintf('COUNT(%s.id) as number', $opportunitiesAlias))
            ->join(Lead::class, 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', $opportunitiesAlias)
            ->orderBy('number', 'DESC')
            ->groupBy('campaign.name');
    }

    public function getCampaignsByCloseRevenueQB(
        string $opportunitiesAlias,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ): QueryBuilder {
        QueryBuilderUtil::checkIdentifier($opportunitiesAlias);
        $qb = $this->createQueryBuilder('campaign');
        $qb
            ->select(
                'campaign.name as label',
                sprintf(
                    'SUM(%s) as closeRevenue',
                    $qbTransformer->getTransformSelectQuery('closeRevenue', $qb, $opportunitiesAlias)
                )
            )
            ->join(Lead::class, 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', $opportunitiesAlias)
            ->where(sprintf(
                'JSON_EXTRACT(%1$s.serialized_data, \'status\') = :status AND %1$s.closeRevenueValue > :revenueVal',
                $opportunitiesAlias
            ))
            ->setParameter('status', 'opportunity_status.won')
            ->setParameter('revenueVal', 0)
            ->orderBy('closeRevenue', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    public function getCount(): int
    {
        $qb = $this->createQueryBuilder('campaign')
            ->select('COUNT(campaign.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
