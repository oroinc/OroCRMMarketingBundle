<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Provider\CampaignEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class CampaignEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    private CampaignEntityNameProvider $provider;

    private Campaign $campaign;

    protected function setUp(): void
    {
        $this->provider = new CampaignEntityNameProvider();

        $this->campaign = new Campaign();
        $this->campaign->setName('test name');
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
            $this->campaign->getName(),
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $this->campaign)
        );
    }

    public function testGetNameDQL(): void
    {
        self::assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, 'en', Campaign::class, 'campaign')
        );
    }
}
