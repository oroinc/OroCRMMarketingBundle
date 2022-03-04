<?php

namespace Oro\Bundle\CampaignBundle\Provider;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

/**
 * Provide title for EmailCampaign entity
 */
class EmailCampaignEntityNameProvider implements EntityNameProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        if (!$entity instanceof EmailCampaign) {
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
