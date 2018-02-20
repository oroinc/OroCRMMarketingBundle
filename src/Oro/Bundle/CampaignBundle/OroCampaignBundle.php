<?php

namespace Oro\Bundle\CampaignBundle;

use Oro\Bundle\CampaignBundle\DependencyInjection\Compiler\TransportPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCampaignBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TransportPass());
    }
}
