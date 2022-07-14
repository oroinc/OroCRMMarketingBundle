<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\EventListener\ConfigListener;
use Oro\Bundle\TrackingBundle\Tools\TrackingDataFolderSelector;
use Oro\Component\Testing\TempDirExtension;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class ConfigListenerTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    /** @var string */
    private $settingsFile;

    /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject */
    private $configManager;

    /** @var RouterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $router;

    /** @var ConfigListener */
    private $listener;

    protected function setUp(): void
    {
        $logsDir = $this->getTempDir('tracking_log');
        $trackingDirSelector = new TrackingDataFolderSelector($logsDir);
        $trackingDir = $trackingDirSelector->retrieve();

        $this->settingsFile = $trackingDir.DIRECTORY_SEPARATOR.'settings.ser';

        $this->configManager = $this->createMock(ConfigManager::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->listener = new ConfigListener(
            $this->configManager,
            $this->router,
            $trackingDirSelector
        );
    }

    public function testOnUpdateAfterNoChanges()
    {
        $event = $this->createMock(ConfigUpdateEvent::class);

        $this->listener->onUpdateAfter($event);
        $this->assertFileDoesNotExist($this->settingsFile);
    }

    public function testOnUpdateAfterNoDynamic()
    {
        $event = $this->createMock(ConfigUpdateEvent::class);

        $event->expects($this->exactly(5))
            ->method('isChanged')
            ->willReturnMap([
                ['oro_tracking.dynamic_tracking_enabled', false],
                ['oro_tracking.log_rotate_interval', true],
                ['oro_tracking.piwik_host', true],
                ['oro_tracking.piwik_token_auth', false],
            ]);

        $event->expects($this->exactly(2))
            ->method('getNewValue')
            ->willReturnMap([
                ['oro_tracking.log_rotate_interval', 5],
                ['oro_tracking.piwik_host', 'http://test.com']
            ]);

        $this->configManager->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap(
                [
                    [
                        'oro_tracking.dynamic_tracking_enabled',
                        false,
                        false,
                        null,
                        ['value' => false, 'scope' => 'app']
                    ],
                    ['oro_tracking.piwik_token_auth', false, false, null, 'TEST_DEFAULT']
                ]
            );

        $this->router->expects($this->never())
            ->method('generate');

        $this->listener->onUpdateAfter($event);
        $this->assertFileExists($this->settingsFile);

        $expectedSettings = [
            'dynamic_tracking_enabled' => false,
            'log_rotate_interval' => 5,
            'piwik_host' => 'http://test.com',
            'piwik_token_auth' => 'TEST_DEFAULT',
            'dynamic_tracking_endpoint' => null,
            'dynamic_tracking_base_url' => null
        ];
        $actualSettings = unserialize(file_get_contents($this->settingsFile));
        $this->assertEquals($expectedSettings, $actualSettings);
    }

    /**
     * @dataProvider dynamicConfigProvider
     */
    public function testOnUpdateAfterDynamic(RequestContext $requestContext, array $expectedSettings)
    {
        $this->router->expects($this->any())
             ->method('getContext')
             ->willReturn($requestContext);

        $event = $this->createMock(ConfigUpdateEvent::class);

        $event->expects($this->exactly(5))
            ->method('isChanged')
            ->willReturnMap([
                ['oro_tracking.dynamic_tracking_enabled', true],
                ['oro_tracking.log_rotate_interval', false],
                ['oro_tracking.piwik_host', false],
                ['oro_tracking.piwik_token_auth', false],
            ]);

        $event->expects($this->once())
            ->method('getNewValue')
            ->willReturnMap([
                ['oro_tracking.dynamic_tracking_enabled', true]
            ]);

        $this->configManager->expects($this->exactly(4))
            ->method('get')
            ->willReturn('default');

        $this->router->expects($this->once())
            ->method('generate')
            ->with('oro_tracking_data_create')
            ->willReturnCallback(function () {
                if (empty($this->router->getContext()->getBaseUrl())) {
                    return '/test/url';
                }

                return sprintf('/%s%s', $this->router->getContext()->getBaseUrl(), '/test/url');
            });

        $this->listener->onUpdateAfter($event);
        $this->assertFileExists($this->settingsFile);

        $actualSettings = unserialize(file_get_contents($this->settingsFile));
        $this->assertEquals($expectedSettings, $actualSettings);
    }

    public function dynamicConfigProvider(): array
    {
        return [
            [
                new RequestContext(),
                [
                    'dynamic_tracking_enabled' => true,
                    'log_rotate_interval' => 'default',
                    'piwik_host' => 'default',
                    'piwik_token_auth' => 'default',
                    'dynamic_tracking_endpoint' => '/test/url',
                    'dynamic_tracking_base_url' => null
                ]
            ],
            [
                new RequestContext('index.php'),
                [
                    'dynamic_tracking_enabled' => true,
                    'log_rotate_interval' => 'default',
                    'piwik_host' => 'default',
                    'piwik_token_auth' => 'default',
                    'dynamic_tracking_endpoint' => '/index.php/test/url',
                    'dynamic_tracking_base_url' => 'index.php'
                ]
            ]
        ];
    }
}
