<?php

namespace Oro\Bundle\MarketingActivityBundle\Placeholder;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

class PlaceholderFilter
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  EntityProvider */
    protected $marketingActivityEntityProvider;

    /**
     * PlaceholderFilter constructor.
     *
     * @param DoctrineHelper $doctrineHelper
     * @param EntityProvider $entityProvider
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        EntityProvider $entityProvider
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->marketingActivityEntityProvider = $entityProvider;
    }

    /**
     * @param integer $campaignId
     *
     * @return boolean
     */
    public function isSummaryApplicable($campaignId)
    {
        return $this->doctrineHelper
                ->getEntityRepository(MarketingActivity::class)
                ->getMarketingActivitySummaryCountByCampaign($campaignId);
    }
}
