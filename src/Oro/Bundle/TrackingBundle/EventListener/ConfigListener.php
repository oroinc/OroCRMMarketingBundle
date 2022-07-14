<?php

namespace Oro\Bundle\TrackingBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Tools\TrackingDataFolderSelector;
use Symfony\Component\Routing\RouterInterface;

/**
 * Updates "settings.ser" file when depended configs are changed.
 */
class ConfigListener
{
    private const DYNAMIC_TRACKING_ROUTE_NAME = 'oro_tracking_data_create';
    private const KEY_PREFIX = 'oro_tracking.';
    private const KEYS = [
        'dynamic_tracking_enabled',
        'dynamic_tracking_base_url',
        'log_rotate_interval',
        'piwik_host',
        'piwik_token_auth'
    ];

    private ConfigManager $configManager;
    private RouterInterface $router;
    private string $logsDir;
    private TrackingDataFolderSelector $trackingDataFolderSelector;

    public function __construct(
        ConfigManager $configManager,
        RouterInterface $router,
        TrackingDataFolderSelector $trackingDataFolderSelector
    ) {
        $this->configManager = $configManager;
        $this->router = $router;
        $this->trackingDataFolderSelector = $trackingDataFolderSelector;
    }

    public function onUpdateAfter(ConfigUpdateEvent $event): void
    {
        $changedData = [];
        foreach (self::KEYS as $key) {
            $configKey = $this->getKeyName($key);
            if ($event->isChanged($configKey)) {
                $changedData[$key] = $event->getNewValue($configKey);
            }
        }

        if ($changedData) {
            $this->updateTrackingConfig($changedData);
        }
    }

    private function updateTrackingConfig(array $configuration): void
    {
        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $configuration)) {
                $value = $this->configManager->get($this->getKeyName($key));
                $value = is_array($value) ? $value['value'] : $value;
                $configuration[$key] = $value;
            }
        }

        if (!empty($configuration['dynamic_tracking_enabled'])) {
            /** This fix remove index.php or other entry point from url if they are present. @see CRM-8338 */
            $baseUrl = $this->router->getContext()->getBaseUrl();
            $configuration['dynamic_tracking_endpoint'] = $this->router->generate(self::DYNAMIC_TRACKING_ROUTE_NAME);
            $configuration['dynamic_tracking_base_url'] = $baseUrl;
        } else {
            $configuration['dynamic_tracking_endpoint'] = null;
            $configuration['dynamic_tracking_base_url'] = null;
        }

        $trackingDir = $this->trackingDataFolderSelector->retrieve();

        if (!is_dir($trackingDir)) {
            mkdir($trackingDir, 0777, true);
        }

        $settingsFile = $trackingDir . DIRECTORY_SEPARATOR . 'settings.ser';
        file_put_contents($settingsFile, serialize($configuration));
    }

    private function getKeyName(string $key): string
    {
        return self::KEY_PREFIX . $key;
    }
}
