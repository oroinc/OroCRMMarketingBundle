<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\DependencyInjection\Compiler;

use Oro\Bundle\CampaignBundle\DependencyInjection\Compiler\TransportPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TransportPassTest extends \PHPUnit\Framework\TestCase
{
    /** @var TransportPass */
    private $compiler;

    protected function setUp(): void
    {
        $this->compiler = new TransportPass();
    }

    public function testProcessWhenNoEmailTransportProvider()
    {
        $container = new ContainerBuilder();

        $this->compiler->process($container);
    }

    public function testProcessWhenNoEmailTransports()
    {
        $container = new ContainerBuilder();
        $emailTransportProviderDef = $container->register('oro_campaign.email_transport.provider');

        $this->compiler->process($container);

        self::assertSame([], $emailTransportProviderDef->getMethodCalls());
    }

    public function testProcess()
    {
        $container = new ContainerBuilder();
        $emailTransportProviderDef = $container->register('oro_campaign.email_transport.provider');

        $container->register('transport_1')
            ->addTag('oro_campaign.email_transport');
        $container->register('transport_2')
            ->addTag('oro_campaign.email_transport');

        $this->compiler->process($container);

        self::assertEquals(
            [
                ['addTransport', [new Reference('transport_1')]],
                ['addTransport', [new Reference('transport_2')]]
            ],
            $emailTransportProviderDef->getMethodCalls()
        );
    }
}
