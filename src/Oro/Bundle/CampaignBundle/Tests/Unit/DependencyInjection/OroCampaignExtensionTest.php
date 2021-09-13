<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CampaignBundle\Controller\Api\Rest\EmailTemplateController;
use Oro\Bundle\CampaignBundle\DependencyInjection\OroCampaignExtension;
use Oro\Bundle\TestFrameworkBundle\Test\DependencyInjection\ExtensionTestCase;

class OroCampaignExtensionTest extends ExtensionTestCase
{
    public function testLoad(): void
    {
        $this->loadExtension(new OroCampaignExtension());

        $expectedDefinitions = [
            EmailTemplateController::class,
        ];

        $this->assertDefinitionsLoaded($expectedDefinitions);
    }
}
