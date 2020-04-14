<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MarketingListUnsubscribedItemControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            [ __NAMESPACE__ . '\\DataFixtures\\LoadMarketingListData']
        );
    }

    public function testCreate()
    {
        $marketingListId = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroMarketingListBundle:MarketingList')
            ->findOneBy([])
            ->getId();

        $this->client->request(
            'POST',
            $this->getUrl('oro_api_post_marketinglist_unsubscribeditem'),
            [
                'entityId'      => 1,
                'marketingList' => $marketingListId
            ]
        );

        $marketingListRemovedItem = $this->getJsonResponseContent(
            $this->client->getResponse(),
            Response::HTTP_CREATED
        );

        return $marketingListRemovedItem['id'];
    }

    /**
     * @depends testCreate
     *
     * @param integer $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_marketinglist_unsubscribeditem', ['id' => $id])
        );

        $this->assertEmptyResponseStatusCodeEquals(
            $this->client->getResponse(),
            Response::HTTP_NO_CONTENT
        );
    }
}
