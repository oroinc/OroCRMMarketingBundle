<?php

namespace Oro\Bundle\MarketingActivityBundle\Placeholder;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureCheckerHolderTrait;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureToggleableInterface;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

class PlaceholderFilter implements FeatureToggleableInterface
{
    use FeatureCheckerHolderTrait;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var  EntityProvider */
    protected $marketingActivityEntityProvider;

    /**
     * PlaceholderFilter constructor.
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
        return $this->isFeaturesEnabled() && $this->doctrineHelper
                ->getEntityRepository(MarketingActivity::class)
                ->getMarketingActivitySummaryCountByCampaign($campaignId);
    }

    /**
     * Checks if the entity can have marketing activities
     *
     * @param object|null $entity
     *
     * @return bool
     */
    public function isApplicable($entity = null)
    {
        if (!$this->isFeaturesEnabled()
            || !is_object($entity)
            || !$this->doctrineHelper->isManageableEntity($entity)
            || $this->doctrineHelper->isNewEntity($entity)
        ) {
            return false;
        }

        $entityClass = $this->doctrineHelper->getEntityClass($entity);
        $supportedActivities = $this->marketingActivityEntityProvider->getEntities();
        foreach ($supportedActivities as $supportedActivity) {
            if (isset($supportedActivity['name']) && $entityClass == $supportedActivity['name']) {
                return true;
            }
        }

        return false;
    }
}
