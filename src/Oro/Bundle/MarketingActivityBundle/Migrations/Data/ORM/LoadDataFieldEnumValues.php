<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

class LoadDataFieldEnumValues extends AbstractEnumFixture
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        return [
            MarketingActivity::TYPE_SEND        => 'Send',
            MarketingActivity::TYPE_OPEN        => 'Open',
            MarketingActivity::TYPE_CLICK       => 'Click',
            MarketingActivity::TYPE_SOFT_BOUNCE => 'Soft Bounce',
            MarketingActivity::TYPE_HARD_BOUNCE => 'Hard Bounce',
            MarketingActivity::TYPE_UNSUBSCRIBE => 'Unsubscribe',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnumCode()
    {
        return 'ma_type';
    }
}
