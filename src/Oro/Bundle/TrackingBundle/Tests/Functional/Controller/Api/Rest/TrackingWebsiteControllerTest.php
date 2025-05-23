<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class TrackingWebsiteControllerTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateApiAuthHeader());

        $this->loadFixtures(
            [
                'Oro\Bundle\TrackingBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadTrackingWebsiteData',
            ]
        );
    }

    public function testDelete()
    {
        $website = $this->getReference('website');

        $this->client->jsonRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_tracking_website', ['id' => $website->getId()]),
            [],
            self::generateApiAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
    }
}
