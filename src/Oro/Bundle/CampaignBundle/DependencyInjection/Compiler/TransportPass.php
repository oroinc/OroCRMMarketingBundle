<?php

namespace Oro\Bundle\CampaignBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers email transport services with the email transport provider during dependency injection compilation.
 */
class TransportPass implements CompilerPassInterface
{
    public const TAG = 'oro_campaign.email_transport';
    public const SERVICE = 'oro_campaign.email_transport.provider';

    #[\Override]
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE)) {
            return;
        }

        $contentProviderManagerDefinition = $container->getDefinition(self::SERVICE);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach (array_keys($taggedServices) as $id) {
            $contentProviderManagerDefinition->addMethodCall(
                'addTransport',
                array(new Reference($id))
            );
        }
    }
}
