<?php

namespace Oro\Bundle\CampaignBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\CampaignBundle\Tests\Functional\DataFixtures\LoadEmailTemplateEntityContactData;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadMarketingListData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class EmailTemplateControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures([LoadEmailTemplateEntityContactData::class, LoadMarketingListData::class]);
    }

    public function testGetWithoutParams(): void
    {
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_emailcampaign_email_templates', ['id' => 0])
        );

        $this->getJsonResponseContent($this->client->getResponse(), 404);
    }

    public function testGet(): void
    {
        $marketingListId = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME)->getId();
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_emailcampaign_email_templates', [
                'id' => $marketingListId
            ])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(2, $result);
    }

    public function testGetNonSystemNoEntity(): void
    {
        /** @var EmailTemplate $template */
        $template = $this->getReference(LoadEmailTemplateEntityContactData::NOT_SYSTEM_ENTITY_NAME_CONTACT);
        $marketingListId = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME)->getId();
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_emailcampaign_email_templates', [
                'id' => $marketingListId,
                'includeNonEntity' => 0,
                'includeSystemTemplates' => 0
            ])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals(
            [
                [
                    'id'          => $template->getId(),
                    'name'        => $template->getName(),
                    'content'     => $template->getContent(),
                    'type'        => $template->getType(),
                    'visible'     => $template->isVisible(),
                    'isSystem'   => $template->getIsSystem(),
                    'isEditable' => $template->getIsEditable(),
                    'entityName' => $template->getEntityName(),
                ]
            ],
            $result
        );
    }

    public function testGetNonSystemEntity(): void
    {
        $marketingListId = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME)->getId();
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_emailcampaign_email_templates', [
                'id' => $marketingListId,
                'includeNonEntity' => 1,
                'includeSystemTemplates' => 0
            ])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(3, $result);
    }

    public function testGetSystemNonEntity(): void
    {
        $marketingListId = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME)->getId();
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_emailcampaign_email_templates', [
                'id' => $marketingListId,
                'includeNonEntity' => 0,
                'includeSystemTemplates' => 1
            ])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(2, $result);
    }

    public function testGetEntitySystem(): void
    {
        $marketingListId = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME)->getId();
        $this->client->jsonRequest(
            'GET',
            $this->getUrl('oro_api_get_emailcampaign_email_templates', [
                'id' => $marketingListId,
                'includeNonEntity' => 1,
                'includeSystemTemplates' => 1
            ])
        );

        $result = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(13, $result);
    }
}
