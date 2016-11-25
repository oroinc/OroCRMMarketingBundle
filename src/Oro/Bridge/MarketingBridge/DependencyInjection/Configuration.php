<?php

namespace Oro\Bridge\MarketingBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('oro_marketing');

        SettingsBuilder::append(
            $rootNode,
            [
                'feature_enabled' => ['value' => true],
            ]
        );

        return $treeBuilder;
    }
}
