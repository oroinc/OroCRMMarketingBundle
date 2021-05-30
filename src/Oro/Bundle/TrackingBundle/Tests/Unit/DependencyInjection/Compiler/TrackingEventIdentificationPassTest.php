<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\TrackingBundle\DependencyInjection\Compiler\TrackingEventIdentificationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TrackingEventIdentificationPassTest extends \PHPUnit\Framework\TestCase
{
    public function testProcessNotRegisterProvider()
    {
        $container = new ContainerBuilder();
        $compilerPass = new TrackingEventIdentificationPass();
        $compilerPass->process($container);
    }

    public function testProcessEmptyProviders()
    {
        $container = new ContainerBuilder();
        $identifierProviderDef = $container->register('oro_tracking.provider.identifier_provider');

        $compilerPass = new TrackingEventIdentificationPass();
        $compilerPass->process($container);
        self::assertSame([], $identifierProviderDef->getMethodCalls());
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $identifierProviderDef = $container->register('oro_tracking.provider.identifier_provider');

        $container->register('provider1')->addTag('oro_tracking.provider.identification');
        $container->register('provider2')->addTag('oro_tracking.provider.identification');
        $container->register('provider3')->addTag('oro_tracking.provider.identification', ['priority' => 100]);
        $container->register('provider4')->addTag('oro_tracking.provider.identification', ['priority' => -100]);

        $compilerPass = new TrackingEventIdentificationPass();
        $compilerPass->process($container);
        self::assertEquals(
            [
                ['addProvider', [new Reference('provider4')]],
                ['addProvider', [new Reference('provider1')]],
                ['addProvider', [new Reference('provider2')]],
                ['addProvider', [new Reference('provider3')]]
            ],
            $identifierProviderDef->getMethodCalls()
        );
    }
}
