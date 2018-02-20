<?php

namespace Oro\Bundle\TrackingBundle;

use Oro\Bundle\TrackingBundle\DependencyInjection\Compiler\TrackingEventIdentificationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroTrackingBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TrackingEventIdentificationPass());
    }
}
