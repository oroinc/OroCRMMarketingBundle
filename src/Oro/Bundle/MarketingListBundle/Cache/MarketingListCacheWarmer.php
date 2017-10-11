<?php

namespace Oro\Bundle\MarketingListBundle\Cache;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

use Oro\Bundle\MarketingListBundle\Provider\MarketingListAllowedClassesProvider;

class MarketingListCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var MarketingListAllowedClassesProvider
     */
    private $provider;

    /**
     * @param MarketingListAllowedClassesProvider $provider
     */
    public function __construct(MarketingListAllowedClassesProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->provider->warmUpCache();
    }
}
