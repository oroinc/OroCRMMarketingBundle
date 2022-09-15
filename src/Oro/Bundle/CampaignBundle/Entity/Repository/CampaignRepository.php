<?php

namespace Oro\Bundle\CampaignBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Doctrine repository for CampaignBundle Campaign entity.
 */
class CampaignRepository extends EntityRepository
{
    /**
     * @param string $code
     *
     * @return Campaign|null
     */
    public function findOneByCode($code)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign')
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join(
                'OroCampaignBundle:CampaignCodeHistory',
                'campaignCodeHistory',
                'WITH',
                'campaignCodeHistory.campaign = campaign'
            )
            ->where('campaignCodeHistory.code = :code')
            ->setParameter('code', $code);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param Campaign $campaign
     * @param bool $excludeCurrent
     * @return array
     */
    public function getCodesHistory(Campaign $campaign, $excludeCurrent = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaignCodeHistory.code')
            ->from('OroCampaignBundle:CampaignCodeHistory', 'campaignCodeHistory')
            ->where('campaignCodeHistory.campaign = :campaign')
            ->setParameter('campaign', $campaign);
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

    /**
     * @param AclHelper $aclHelper
     * @param int       $recordsCount
     * @param array     $dateRange
     *
     * @return array
     */
    public function getCampaignsLeads(AclHelper $aclHelper, $recordsCount, $dateRange = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign.name as label', 'COUNT(lead.id) as number', 'MAX(campaign.createdAt) as maxCreated')
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->leftJoin('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
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
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(
            'campaign.name as label',
            sprintf('COUNT(%s.id) as number', $leadAlias),
            'MAX(campaign.createdAt) as maxCreated'
        )
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->leftJoin('OroSalesBundle:Lead', $leadAlias, 'WITH', sprintf('%s.campaign = campaign', $leadAlias))
            ->orderBy('maxCreated', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    /**
     * @param AclHelper $aclHelper
     * @param int       $recordsCount
     * @param array     $dateRange
     *
     * @return array
     */
    public function getCampaignsOpportunities(AclHelper $aclHelper, $recordsCount, $dateRange = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign.name as label', 'COUNT(opportunities.id) as number')
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
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

    /**
     * @param string $opportunitiesAlias
     *
     * @return QueryBuilder
     */
    public function getCampaignsOpportunitiesQB($opportunitiesAlias)
    {
        QueryBuilderUtil::checkIdentifier($opportunitiesAlias);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('campaign.name as label', sprintf('COUNT(%s.id) as number', $opportunitiesAlias))
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', $opportunitiesAlias)
            ->orderBy('number', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    /**
     * @param string $opportunitiesAlias
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     *
     * @return QueryBuilder
     */
    public function getCampaignsByCloseRevenueQB(
        $opportunitiesAlias,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        QueryBuilderUtil::checkIdentifier($opportunitiesAlias);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $crSelect = $qbTransformer->getTransformSelectQuery('closeRevenue', $qb, $opportunitiesAlias);
        $qb
            ->select(
                'campaign.name as label',
                sprintf(
                    'SUM(%s) as closeRevenue',
                    $crSelect
                )
            )
            ->from('OroCampaignBundle:Campaign', 'campaign')
            ->join('OroSalesBundle:Lead', 'lead', 'WITH', 'lead.campaign = campaign')
            ->join('lead.opportunities', $opportunitiesAlias)
            ->where(sprintf('%s.status=\'won\'', $opportunitiesAlias))
            ->andWhere(sprintf('%s.closeRevenueValue>0', $opportunitiesAlias))
            ->orderBy('closeRevenue', 'DESC')
            ->groupBy('campaign.name');

        return $qb;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
