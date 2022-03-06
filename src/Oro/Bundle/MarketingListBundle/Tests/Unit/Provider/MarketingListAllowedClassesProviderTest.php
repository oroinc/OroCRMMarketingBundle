<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class MarketingListAllowedClassesProviderTest extends \PHPUnit\Framework\TestCase
{
    private CacheInterface $cacheProvider;
    private EntityProvider $entityProvider;

    protected function setUp(): void
    {
        $this->cacheProvider = $this->createMock(CacheInterface::class);

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
        $this->cacheProvider->expects($this->once())
            ->method('delete')
            ->with('oro_marketing_list.allowed_entities');
        $this->cacheProvider->expects($this->once())
            ->method('get')
            ->with('oro_marketing_list.allowed_entities')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });
        $this->provider->warmUpCache();
    }

    public function testGetListCached()
    {
        $this->cacheProvider->expects($this->once())
            ->method('get')
            ->with('oro_marketing_list.allowed_entities')
            ->willReturn($this->getCachedAllowedEntities());

        $entities = $this->provider->getList();

        $this->assertEquals($this->getCachedAllowedEntities(), $entities);
    }

    public function testGetListNotCached()
    {
        $this->cacheProvider->expects($this->once())
            ->method('get')
            ->with('oro_marketing_list.allowed_entities')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });
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
