<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;
use Oro\Bundle\TrackingBundle\Controller\Api\Rest\TrackingWebsiteController;
use Oro\Bundle\TrackingBundle\DependencyInjection\OroTrackingExtension;

class OroTrackingExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $expectedDefinitions = [
            TrackingWebsiteController::class,
        ];

        $this->loadExtension(new OroTrackingExtension());
        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
