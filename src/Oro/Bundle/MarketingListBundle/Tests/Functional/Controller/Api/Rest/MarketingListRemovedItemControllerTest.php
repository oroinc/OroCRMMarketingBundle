<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadMarketingListData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MarketingListRemovedItemControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
        $this->loadFixtures([LoadMarketingListData::class]);
    }

    public function testCreate()
    {
        $marketingListId = $this->getContainer()->get('doctrine')->getRepository(MarketingList::class)
            ->findOneBy([])
            ->getId();

        $this->client->jsonRequest(
            'POST',
            $this->getUrl('oro_api_post_marketinglist_removeditem'),
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
     */
    public function testDelete(int $id)
    {
        $this->client->jsonRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_marketinglist_removeditem', ['id' => $id])
        );

        $this->assertEmptyResponseStatusCodeEquals(
            $this->client->getResponse(),
            Response::HTTP_NO_CONTENT
        );
    }
}
