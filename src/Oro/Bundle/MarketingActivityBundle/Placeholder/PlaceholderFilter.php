<?php

namespace Oro\Bundle\MarketingActivityBundle\Placeholder;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;

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
     * Checks if the entity can have marketing activities
     *
     * @param object|null $entity
     *
     * @return bool
     */
    public function isApplicable($entity = null)
    {
        if (!is_object($entity)
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
