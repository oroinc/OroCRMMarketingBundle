<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\TrackingBundle\DependencyInjection\OroTrackingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroTrackingExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroTrackingExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'dynamic_tracking_enabled' => ['value' => false, 'scope' => 'app'],
                        'log_rotate_interval' => ['value' => 60, 'scope' => 'app'],
                        'piwik_host' => ['value' => null, 'scope' => 'app'],
                        'piwik_token_auth' => ['value' => null, 'scope' => 'app'],
                        'feature_enabled' => ['value' => true, 'scope' => 'app'],
                        'precalculated_statistic_enabled' => ['value' => true, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_tracking')
        );
    }
}
