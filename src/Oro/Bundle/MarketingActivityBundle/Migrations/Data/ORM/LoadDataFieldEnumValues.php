<?php

namespace Oro\Bundle\MarketingActivityBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

/**
 * Loads data field enum options
 */
class LoadDataFieldEnumValues extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            MarketingActivity::TYPE_SEND => 'Send',
            MarketingActivity::TYPE_OPEN => 'Open',
            MarketingActivity::TYPE_CLICK => 'Click',
            MarketingActivity::TYPE_SOFT_BOUNCE => 'Soft Bounce',
            MarketingActivity::TYPE_HARD_BOUNCE => 'Hard Bounce',
            MarketingActivity::TYPE_UNSUBSCRIBE => 'Unsubscribe',
        ];
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return 'ma_type';
    }
}
