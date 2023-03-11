<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\CampaignBundle\DependencyInjection\OroCampaignExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCampaignExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

        $extension = new OroCampaignExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'campaign_sender_email' => [
                            'value' => sprintf('no-reply@%s.example', gethostname()),
                            'scope' => 'app'
                        ],
                        'campaign_sender_name' => ['value' => 'Oro', 'scope' => 'app'],
                        'feature_enabled' => ['value' => true, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_campaign')
        );
    }
}
