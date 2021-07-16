<?php

namespace Oro\Bundle\MarketingListBundle\DependencyInjection\Compiler;

use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GridCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->findDefinition('oro_tag.grid.tags_extension')
            ->addMethodCall('addUnsupportedGridPrefix', [ConfigurationProvider::GRID_PREFIX]);
        $container->findDefinition('oro_tag.grid.tags_report_extension')
            ->addMethodCall('addUnsupportedGridPrefix', [ConfigurationProvider::GRID_PREFIX]);
    }
}
