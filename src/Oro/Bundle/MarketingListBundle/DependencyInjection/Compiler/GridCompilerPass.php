<?php

namespace Oro\Bundle\MarketingListBundle\DependencyInjection\Compiler;

use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Registers marketing list grid prefixes as unsupported for tag extensions during dependency injection compilation.
 */
class GridCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container)
    {
        $container->findDefinition('oro_tag.grid.tags_extension')
            ->addMethodCall('addUnsupportedGridPrefix', [ConfigurationProvider::GRID_PREFIX]);
        $container->findDefinition('oro_tag.grid.tags_report_extension')
            ->addMethodCall('addUnsupportedGridPrefix', [ConfigurationProvider::GRID_PREFIX]);
    }
}
