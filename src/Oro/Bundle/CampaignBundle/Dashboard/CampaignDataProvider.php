<?php

namespace Oro\Bundle\CampaignBundle\Dashboard;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\Repository\CampaignRepository;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Provides dashboard data related to Campaign entity.
 */
class CampaignDataProvider
{
    public const CAMPAIGN_LEAD_COUNT          = 5;
    public const CAMPAIGN_OPPORTUNITY_COUNT   = 5;
    public const CAMPAIGN_CLOSE_REVENUE_COUNT = 5;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /** @var CurrencyQueryBuilderTransformerInterface  */
    protected $qbTransformer;

    public function __construct(
        ManagerRegistry $doctrine,
        AclHelper $aclHelper,
        DateFilterProcessor $processor,
        CurrencyQueryBuilderTransformerInterface $qbTransformer
    ) {
        $this->registry            = $doctrine;
        $this->aclHelper           = $aclHelper;
        $this->dateFilterProcessor = $processor;
        $this->qbTransformer       = $qbTransformer;
    }

    public function getCampaignLeadsData(
        array $dateRange,
        bool $hideCampaign = true,
        int $maxResults = self::CAMPAIGN_LEAD_COUNT
    ): array {
        $dateRange['in_group'] = true;
        $qb = $this->getCampaignRepository()->getCampaignsLeadsQB('lead');
        if ($hideCampaign) {
            $qb->where($qb->expr()->isNotNull('lead'));
        }
        $qb->setMaxResults($maxResults);
        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'lead.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    public function getCampaignOpportunitiesData(
        array $dateRange,
        int $maxResults = self::CAMPAIGN_OPPORTUNITY_COUNT
    ): array {
        $dateRange['in_group'] = true;
        $qb = $this->getCampaignRepository()->getCampaignsOpportunitiesQB('opportunities');
        $qb->setMaxResults($maxResults);
        $this->dateFilterProcessor->process($qb, $dateRange, 'opportunities.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    public function getCampaignsByCloseRevenueData(
        array $dateRange,
        int $maxResults = self::CAMPAIGN_CLOSE_REVENUE_COUNT
    ): array {
        $dateRange['in_group'] = true;
        $qb = $this->getCampaignRepository()->getCampaignsByCloseRevenueQB(
            'opportunities',
            $this->qbTransformer
        );
        $qb->setMaxResults($maxResults);
        $this->dateFilterProcessor->applyDateRangeFilterToQuery($qb, $dateRange, 'opportunities.createdAt');

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    protected function getCampaignRepository(): CampaignRepository
    {
        return $this->registry->getRepository(Campaign::class);
    }
}
