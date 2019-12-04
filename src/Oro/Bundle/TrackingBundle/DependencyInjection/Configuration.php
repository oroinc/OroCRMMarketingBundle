<?php

namespace Oro\Bundle\TrackingBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oro_tracking');
        $rootNode = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            array(
                'dynamic_tracking_enabled' => ['value' => false],
                'log_rotate_interval'      => ['value' => 60],
                'piwik_host'               => ['value' => null],
                'piwik_token_auth'         => ['value' => null],
                'feature_enabled'          => ['value' => true],
                'precalculated_statistic_enabled' => ['value' => true],
            )
        );

        return $treeBuilder;
    }
}
