<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListEntityNameProvider;

class MarketingListEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    private MarketingListEntityNameProvider $provider;

    private MarketingList $marketingList;

    protected function setUp(): void
    {
        $this->provider = new MarketingListEntityNameProvider();

        $this->marketingList = new MarketingList();
        $this->marketingList->setName('test name');
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
            $this->marketingList->getName(),
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $this->marketingList)
        );
    }

    public function testGetNameDQL(): void
    {
        self::assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, 'en', MarketingList::class, 'campaign')
        );
    }
}
