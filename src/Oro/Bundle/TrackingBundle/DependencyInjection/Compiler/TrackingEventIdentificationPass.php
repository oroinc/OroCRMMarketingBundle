<?php

namespace Oro\Bundle\TrackingBundle\DependencyInjection\Compiler;

use Oro\Component\DependencyInjection\Compiler\TaggedServicesCompilerPassTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Collects tracking provider identifier services by `oro_tracking.provider.identification` tag,
 * sorts them and inject to the `oro_tracking.provider.identifier_provider`
 */
class TrackingEventIdentificationPass implements CompilerPassInterface
{
    use TaggedServicesCompilerPassTrait;

    private const PROVIDER_SERVICE_ID = 'oro_tracking.provider.identifier_provider';
    private const TAG_NAME            = 'oro_tracking.provider.identification';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_SERVICE_ID)) {
            return;
        }

        $service = $container->getDefinition(self::PROVIDER_SERVICE_ID);
        $taggedServices = $this->findAndInverseSortTaggedServices(self::TAG_NAME, $container);
        foreach ($taggedServices as $taggedService) {
            $service->addMethodCall('addProvider', [$taggedService]);
        }
    }
}
