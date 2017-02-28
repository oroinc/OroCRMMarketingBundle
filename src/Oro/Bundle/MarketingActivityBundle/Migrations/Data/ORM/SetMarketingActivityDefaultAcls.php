<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Data\ORM;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;

class SetMarketingActivityDefaultAcls extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    protected function getDataPath()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAclData()
    {
        return [
            self::ALL_ROLES => [
                'permissions' => [
                    'entity|Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity' => ['VIEW_SYSTEM']
                ]
            ]
        ];
    }
}
