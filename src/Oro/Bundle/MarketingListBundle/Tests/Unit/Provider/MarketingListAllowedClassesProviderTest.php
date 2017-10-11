<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MarketingListAllowedClassesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @var EntityProvider
     */
    private $entityProvider;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        $this->cacheProvider = new ArrayCache();

        $this->entityProvider = $this->getMockBuilder(EntityProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityProvider->expects($this->any())
            ->method('getEntities')
            ->willReturn($this->getAllowedEntities());

        $this->container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $this->container->expects($this->any())
            ->method('get')
            ->with('oro_marketing_list.entity_provider.contact_information')
            ->willReturn($this->entityProvider);

        $this->provider = new MarketingListAllowedClassesProvider(
            $this->cacheProvider,
            $this->container
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

    /**
     * @return array
     */
    private function getCachedAllowedEntities(): array
    {
        return [
            User::class,
            Organization::class,
        ];
    }
}
