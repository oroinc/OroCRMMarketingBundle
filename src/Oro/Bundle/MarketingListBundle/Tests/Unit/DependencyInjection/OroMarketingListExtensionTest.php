<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MarketingListBundle\Controller\Api\Rest as Api;
use Oro\Bundle\MarketingListBundle\DependencyInjection\OroMarketingListExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroMarketingListExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroMarketingListExtension());

        $expectedDefinitions = [
            Api\MarketingListController::class,
            Api\MarketingListRemovedItemController::class,
            Api\MarketingListUnsubscribedItemController::class,
            Api\SegmentController::class,
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
