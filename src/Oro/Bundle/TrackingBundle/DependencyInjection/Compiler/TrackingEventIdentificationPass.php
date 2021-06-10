<?php

namespace Oro\Bundle\TrackingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects tracking provider identifier services by `oro_tracking.provider.identification` tag,
 * sorts them and inject to the `oro_tracking.provider.identifier_provider`
 */
class TrackingEventIdentificationPass implements CompilerPassInterface
{
    const TAG = 'oro_tracking.provider.identification';

    const PROVIDER_SERVICE_ID = 'oro_tracking.provider.identifier_provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_SERVICE_ID)) {
            return;
        }

        // find providers
        $providers      = [];
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $id => $attributes) {
            $priority               = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $providers[$priority][] = new Reference($id);
        }
        if (empty($providers)) {
            return;
        }

        // sort by priority and flatten
        ksort($providers);
        $providers = array_merge(...array_values($providers));

        // register
        $serviceDef = $container->getDefinition(self::PROVIDER_SERVICE_ID);
        foreach ($providers as $provider) {
            $serviceDef->addMethodCall('addProvider', [$provider]);
        }
    }
}
