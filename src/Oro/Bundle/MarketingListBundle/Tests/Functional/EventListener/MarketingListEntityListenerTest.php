<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\EventListener;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadMarketingListData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class MarketingListEntityListenerTest extends WebTestCase
{
    private const CACHE_KEY = 'some';
    private const CACHE_VALUE = 'value';

    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
    }

    public function testPostUpdateCacheInvalidation()
    {
        $this->loadFixtures([LoadMarketingListData::class]);

        $this->getCacheProvider()->save(self::CACHE_KEY, self::CACHE_VALUE);
        $this->assertTrue($this->getCacheProvider()->contains(self::CACHE_KEY));

        /** @var MarketingList $marketingList */
        $marketingList = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME);

        $marketingList->setName('some_new_name');
        $this->getEntityManager()->flush();

        $this->assertFalse($this->getCacheProvider()->contains(self::CACHE_KEY));
    }

    public function testPostPersistCacheInvalidation()
    {
        $this->getCacheProvider()->save(self::CACHE_KEY, self::CACHE_VALUE);
        $this->assertTrue($this->getCacheProvider()->contains(self::CACHE_KEY));

        $this->loadFixtures([LoadMarketingListData::class]);

        $this->assertFalse($this->getCacheProvider()->contains(self::CACHE_KEY));
    }

    public function testPostRemoveCacheInvalidation()
    {
        $this->loadFixtures([LoadMarketingListData::class]);

        $this->getCacheProvider()->save(self::CACHE_KEY, self::CACHE_VALUE);
        $this->assertTrue($this->getCacheProvider()->contains(self::CACHE_KEY));

        $marketingList = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME);

        $this->getEntityManager()->remove($marketingList);
        $this->getEntityManager()->flush();

        $this->assertFalse($this->getCacheProvider()->contains(self::CACHE_KEY));
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(MarketingList::class);
    }

    private function getCacheProvider(): CacheProvider
    {
        return self::getContainer()->get('oro_marketing_list.virtual_relation_cache');
    }
}
