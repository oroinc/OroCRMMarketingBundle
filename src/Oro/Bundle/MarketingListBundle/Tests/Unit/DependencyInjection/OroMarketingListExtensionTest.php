<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MarketingListBundle\DependencyInjection\OroMarketingListExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroMarketingListExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroMarketingListExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'feature_enabled' => ['value' => true, 'scope' => 'app']
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_marketing_list')
        );
    }
}
