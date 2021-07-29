<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class TrackingWebsiteControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());

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
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
    }
}
