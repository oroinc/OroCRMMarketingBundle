<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest\DataFixtures\LoadMarketingListData;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Cache\CacheInterface;

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
        $this->getCacheProvider()->clear();
    }

    public function testPostUpdateCacheInvalidation()
    {
        $this->loadFixtures([LoadMarketingListData::class]);

        $cacheValue = $this->getCacheProvider()->get(self::CACHE_KEY, function () {
            return self::CACHE_VALUE;
        });
        $this->assertEquals(self::CACHE_VALUE, $cacheValue);

        /** @var MarketingList $marketingList */
        $marketingList = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME);

        $marketingList->setName('some_new_name');
        $this->getEntityManager()->flush();
    }

    public function testPostPersistCacheInvalidation()
    {
        $cacheValue = $this->getCacheProvider()->get(self::CACHE_KEY, function () {
            return self::CACHE_VALUE;
        });
        $this->assertEquals(self::CACHE_VALUE, $cacheValue);

        $this->loadFixtures([LoadMarketingListData::class]);
    }

    public function testPostRemoveCacheInvalidation()
    {
        $this->loadFixtures([LoadMarketingListData::class]);

        $cacheValue = $this->getCacheProvider()->get(self::CACHE_KEY, function () {
            return self::CACHE_VALUE;
        });
        $this->assertEquals(self::CACHE_VALUE, $cacheValue);

        $marketingList = $this->getReference(LoadMarketingListData::MARKETING_LIST_NAME);

        $this->getEntityManager()->remove($marketingList);
        $this->getEntityManager()->flush();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(MarketingList::class);
    }

    private function getCacheProvider(): CacheInterface
    {
        return self::getContainer()->get('oro_marketing_list.virtual_relation_cache');
    }
}
