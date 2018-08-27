<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The provider that can be used to get a list of entities are allowed to be used in marketing lists.
 */
class MarketingListAllowedClassesProvider
{
    const MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY = 'oro_marketing_list.allowed_entities';

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param CacheProvider $cacheProvider
     * @param ContainerInterface $container
     */
    public function __construct(
        CacheProvider $cacheProvider,
        ContainerInterface $container
    ) {
        $this->cacheProvider = $cacheProvider;
        $this->container = $container;
    }

    /**
     * @return string[]
     */
    public function getList(): array
    {
        $entitiesList = $this->cacheProvider->fetch(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY);
        if (false === $entitiesList) {
            $entitiesList = $this->getEntitiesList();
            $this->cacheProvider->save(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY, $entitiesList);
        }

        return $entitiesList;
    }

    public function warmUpCache()
    {
        $this->cacheProvider->save(
            static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY,
            $this->getEntitiesList()
        );
    }

    /**
     * @return string[]
     */
    private function getEntitiesList(): array
    {
        $entities = $this->getEntityProvider()->getEntities(false, true, false);

        return $this->extractEntitiesNames($entities);
    }

    /**
     * @param array $entities
     * @return string[]
     */
    private function extractEntitiesNames($entities): array
    {
        return array_map(function ($entity) {
            return $entity['name'];
        }, $entities);
    }

    /**
     * Unable to inject it because of circular references
     *
     * @return EntityProvider
     */
    private function getEntityProvider(): EntityProvider
    {
        return $this->container->get('oro_marketing_list.entity_provider.contact_information');
    }
}
