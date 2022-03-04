<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Provider\EmailCampaignEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class EmailCampaignEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    private EmailCampaignEntityNameProvider $provider;

    private EmailCampaign $emailCampaign;

    protected function setUp(): void
    {
        $this->provider = new EmailCampaignEntityNameProvider();

        $this->emailCampaign = new EmailCampaign();
        $this->emailCampaign->setName('test name');
    }

    public function testGetNameForUnsupportedEntity(): void
    {
        self::assertFalse(
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', new \stdClass())
        );
    }

    public function testGetName(): void
    {
        self::assertEquals(
            $this->emailCampaign->getName(),
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $this->emailCampaign)
        );
    }

    public function testGetNameDQL(): void
    {
        self::assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, 'en', EmailCampaign::class, 'campaign')
        );
    }
}
