<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MarketingListControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());
    }

    public function testDelete()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $type = $em->getRepository(MarketingListType::class)
            ->find(MarketingListType::TYPE_DYNAMIC);

        $entity = new MarketingList();
        $entity
            ->setType($type)
            ->setName('list_name')
            ->setEntity('entity');

        $em->persist($entity);
        $em->flush($entity);

        $this->assertNotNull($entity->getId());

        $this->client->jsonRequest(
            'DELETE',
            $this->getUrl('oro_api_delete_marketinglist', ['id' => $entity->getId()]),
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, Response::HTTP_NO_CONTENT);
    }
}
