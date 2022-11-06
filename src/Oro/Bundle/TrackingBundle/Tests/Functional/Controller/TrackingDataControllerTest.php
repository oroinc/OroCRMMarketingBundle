<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class TrackingDataControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testCreate(array $options)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_tracking_data_create', $options),
            [],
            [],
            $this->generateBasicAuthHeader()
        );
        $response = $this->client->getResponse();
        $result   = $this->getJsonResponseContent($response, 201);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayNotHasKey('validation', $result);

        $this->runCommand('oro:cron:batch:cleanup', ['-i' => '1 day']);
    }

    public function optionsProvider(): array
    {
        return [
            'simple' => [
                [
                    'param'          => 'value',
                    'url'            => 'example.com',
                    'userIdentifier' => 'username',
                    'loggedAt'       => '2014-07-18T15:00:00+0300'
                ]
            ],
            'event'  => [
                [
                    'param'          => 'value',
                    'name'           => 'name',
                    'userIdentifier' => 'username',
                    'url'            => 'example.com',
                    'loggedAt'       => '2014-07-18T15:00:00+0300'
                ]
            ],
        ];
    }

    /**
     * @dataProvider validationProvider
     */
    public function testValidation(array $options, array $expectedMessages)
    {
        $this->client->request(
            'GET',
            $this->getUrl('oro_tracking_data_create', $options),
            [],
            [],
            $this->generateBasicAuthHeader()
        );
        $response = $this->client->getResponse();
        $result   = $this->getJsonResponseContent($response, 400);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayNotHasKey('errors', $result);
        $errors = implode(', ', $result['validation']);
        foreach ($expectedMessages as $expectedMessage) {
            self::assertStringContainsString($expectedMessage, $errors);
        }

        $this->runCommand('oro:cron:batch:cleanup', ['-i' => '1 day']);
    }

    public function validationProvider(): array
    {
        return [
            'empty'          => [
                [],
                [
                    'event.userIdentifier: This value should not be blank',
                    'event.url: This value should not be blank',
                    'event.loggedAt: This value should not be blank',
                ]
            ],
            'userIdentifier' => [
                [
                    'userIdentifier' => 'user_identifier'
                ],
                [
                    'event.url: This value should not be blank',
                    'event.loggedAt: This value should not be blank',
                ]
            ],
            'url'            => [
                [
                    'userIdentifier' => 'user_identifier',
                    'url'            => 'example.com'
                ],
                [
                    'event.loggedAt: This value should not be blank',
                ]
            ],
        ];
    }
}
