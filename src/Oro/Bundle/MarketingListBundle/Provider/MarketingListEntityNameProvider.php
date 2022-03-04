<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * Provide title for MarketingList entity
 */
class MarketingListEntityNameProvider implements EntityNameProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        if (!$entity instanceof MarketingList) {
            return false;
        }

        return $entity->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        return false;
    }
}
