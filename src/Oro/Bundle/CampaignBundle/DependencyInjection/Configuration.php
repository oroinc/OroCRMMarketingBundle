<?php

namespace Oro\Bundle\CampaignBundle\DependencyInjection;

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
        $treeBuilder = new TreeBuilder('oro_campaign');
        $rootNode    = $treeBuilder->getRootNode();

        SettingsBuilder::append(
            $rootNode,
            [
                'campaign_sender_email' => ['value' => sprintf('no-reply@%s.example', gethostname())],
                'campaign_sender_name'  => ['value' => 'Oro'],
                'feature_enabled' => ['value' => true],
            ]
        );

        return $treeBuilder;
    }
}
