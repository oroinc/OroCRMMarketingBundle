<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Component\Config\Cache\WarmableConfigCacheInterface;

/**
 * The provider that can be used to get a list of entities are allowed to be used in marketing lists.
 */
class MarketingListAllowedClassesProvider implements WarmableConfigCacheInterface
{
    const MARKETING_LIST_ALLOWED_ENTITIES_CACHE_KEY = 'oro_marketing_list.allowed_entities';

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @var EntityProvider
     */
    private $entityProvider;

    public function __construct(
        CacheProvider $cacheProvider,
        EntityProvider $entityProvider
    ) {
        $this->cacheProvider = $cacheProvider;
        $this->entityProvider = $entityProvider;
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

    /**
     * {@inheritdoc}
     */
    public function warmUpCache(): void
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
        $entities = $this->entityProvider->getEntities(false, true, false);

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
}
