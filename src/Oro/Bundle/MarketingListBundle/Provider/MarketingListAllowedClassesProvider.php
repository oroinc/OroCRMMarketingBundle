<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Component\Config\Cache\WarmableConfigCacheInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * The provider that can be used to get a list of entities are allowed to be used in marketing lists.
 */
class MarketingListAllowedClassesProvider implements WarmableConfigCacheInterface
{
    private const MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY = 'oro_marketing_list.allowed_entities';

    private CacheInterface $cacheProvider;
    private EntityProvider $entityProvider;

    public function __construct(
        CacheInterface $cacheProvider,
        EntityProvider $entityProvider
    ) {
        $this->cacheProvider = $cacheProvider;
        $this->entityProvider = $entityProvider;
    }

    public function getList(): array
    {
        return $this->cacheProvider->get(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY, function () {
            return $this->getEntitiesList();
        });
    }

    public function warmUpCache(): void
    {
        $this->cacheProvider->delete(static::MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY);
        $this->getList();
    }

    private function getEntitiesList(): array
    {
        $entities = $this->entityProvider->getEntities(false, true, false);

        return $this->extractEntitiesNames($entities);
    }

    private function extractEntitiesNames(array $entities): array
    {
        return array_map(function ($entity) {
            return $entity['name'];
        }, $entities);
    }
}
