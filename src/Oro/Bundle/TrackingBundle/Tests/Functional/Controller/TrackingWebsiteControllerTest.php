<?php

namespace Oro\Bundle\TrackingBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class TrackingWebsiteControllerTest extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        gc_disable();
    }

    public static function tearDownAfterClass(): void
    {
        gc_enable();
    }

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    public function testCreate()
    {
        $crawler                                  = $this->client->request(
            'GET',
            $this->getUrl('oro_tracking_website_create')
        );

        $form                                     = $crawler->selectButton('Save and Close')->form();
        $form['oro_tracking_website[name]']       = 'name';
        $form['oro_tracking_website[identifier]'] = 'unique';
        $form['oro_tracking_website[url]']        = 'http://example.com';
        $form['oro_tracking_website[owner]']      = '1';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Tracking Website saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testView()
    {
        $response = $this->client->requestGrid(
            'website-grid'
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);

        $this->client->request(
            'GET',
            $this->getUrl('oro_tracking_website_view', ['id' => $result['id']])
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
    }

    /**
     * @depends testCreate
     *
     * @return int
     */
    public function testGetTrackIdByIdentifier()
    {
        $response = $this->client->requestGrid(
            'website-grid',
            ['website-grid[_filter][identifier][value]' => 'unique']
        );

        $result = $this->getJsonResponseContent($response, 200);
        $result = reset($result['data']);
        return $result['id'];
    }

    /**
     * @depends testGetTrackIdByIdentifier
     *
     * @param int $id
     */
    public function testUpdate($id)
    {
        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_tracking_website_update', ['id' => $id])
        );

        $form                                     = $crawler->selectButton('Save and Close')->form();
        $form['oro_tracking_website[name]']       = 'nameUP';
        $form['oro_tracking_website[identifier]'] = 'unique2';
        $form['oro_tracking_website[url]']        = 'http://example.org';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Tracking Website saved", $crawler->html());
    }

    /**
     * @depends testUpdate
     */
    public function testIndex()
    {
        $this->client->request('GET', $this->getUrl('oro_tracking_website_index'));
        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString('unique2', $result->getContent());
    }
}
