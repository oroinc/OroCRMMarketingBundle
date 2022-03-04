<?php

namespace Oro\Bundle\CampaignBundle\Provider;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * Provide title for Campaign entity
 */
class CampaignEntityNameProvider implements EntityNameProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        if (!$entity instanceof Campaign) {
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
