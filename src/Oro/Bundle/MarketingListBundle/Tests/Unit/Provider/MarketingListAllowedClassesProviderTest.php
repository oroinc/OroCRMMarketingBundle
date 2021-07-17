<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

class MarketingListAllowedClassesProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @var EntityProvider
     */
    private $entityProvider;

    protected function setUp(): void
    {
        $this->cacheProvider = new ArrayCache();

        $this->entityProvider = $this->createMock(EntityProvider::class);
        $this->entityProvider->expects($this->any())
            ->method('getEntities')
            ->willReturn($this->getAllowedEntities());

        $this->provider = new MarketingListAllowedClassesProvider(
            $this->cacheProvider,
            $this->entityProvider
        );
    }

    public function testWarmUpCache()
    {
        $this->provider->warmUpCache();
        $this->assertEquals(
            $this->getCachedAllowedEntities(),
            $this->cacheProvider->fetch(MarketingListAllowedClassesProvider::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY)
        );
    }

    public function testGetListCached()
    {
        $this->cacheProvider->save(
            MarketingListAllowedClassesProvider::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY,
            $this->getCachedAllowedEntities()
        );

        $entities = $this->provider->getList();

        $this->assertEquals($this->getCachedAllowedEntities(), $entities);
    }

    public function testGetListNotCached()
    {
        $entities = $this->provider->getList();

        $this->assertEquals($this->getCachedAllowedEntities(), $entities);
    }

    /**
     * @return string[]
     */
    private function getAllowedEntities(): array
    {
        return [
            ['name' => User::class],
            ['name' => Organization::class],
        ];
    }

    private function getCachedAllowedEntities(): array
    {
        return [
            User::class,
            Organization::class,
        ];
    }
}
