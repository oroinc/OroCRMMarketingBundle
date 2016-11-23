<?php

namespace Oro\Bridge\MarketingBridge\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class OroMarketingBridgeExtension extends Extension
{
    const ALIAS = 'oro_marketing';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
    }

    /**
    * {@inheritDoc}
    */
    public function getAlias()
    {
        return self::ALIAS;
    }
}
