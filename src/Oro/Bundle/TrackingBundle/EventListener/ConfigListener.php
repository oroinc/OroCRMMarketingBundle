<?php

namespace Oro\Bundle\TrackingBundle\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\TrackingBundle\Tools\TrackingDataFolderSelector;
use Symfony\Component\Routing\Router;

/**
 * Updates "settings.ser" file when depended configs are changed.
 */
class ConfigListener
{
    /**
     * @var string
     */
    protected $dynamicTrackingRouteName = 'oro_tracking_data_create';

    /**
     * @var string
     */
    protected $prefix = 'oro_tracking';

    /**
     * @var array
     */
    protected $keys = array(
        'dynamic_tracking_enabled',
        'dynamic_tracking_base_url',
        'log_rotate_interval',
        'piwik_host',
        'piwik_token_auth'
    );

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $logsDir;

    private ?TrackingDataFolderSelector $trackingDataFolderSelector = null;


    /**
     * @param ConfigManager $configManager
     * @param Router $router
     * @param string $logsDir
     */
    public function __construct(
        ConfigManager $configManager,
        Router $router,
        $logsDir
    ) {
        $this->configManager = $configManager;
        $this->router = $router;
        $this->logsDir = $logsDir;
    }

    public function setTrackingDataFolderSelector(?TrackingDataFolderSelector $trackingDataFolderSelector = null)
    {
        $this->trackingDataFolderSelector = $trackingDataFolderSelector;
    }

    public function onUpdateAfter(ConfigUpdateEvent $event)
    {
        $changedData = array();
        foreach ($this->keys as $key) {
            $configKey = $this->getKeyName($key);
            if ($event->isChanged($configKey)) {
                $changedData[$key] = $event->getNewValue($configKey);
            }
        }

        if ($changedData) {
            $this->updateTrackingConfig($changedData);
        }
    }

    protected function updateTrackingConfig(array $configuration)
    {
        foreach ($this->keys as $key) {
            if (!array_key_exists($key, $configuration)) {
                $value = $this->configManager->get($this->getKeyName($key));
                $value = is_array($value) ? $value['value'] : $value;
                $configuration[$key] = $value;
            }
        }

        if (!empty($configuration['dynamic_tracking_enabled'])) {
            /** This fix remove index.php or other entry point from url if they are present. @see CRM-8338 */
            $baseUrl = $this->router->getContext()->getBaseUrl();
            $configuration['dynamic_tracking_endpoint'] = $this->router->generate($this->dynamicTrackingRouteName);
            $configuration['dynamic_tracking_base_url'] = $baseUrl;
        } else {
            $configuration['dynamic_tracking_endpoint'] = null;
            $configuration['dynamic_tracking_base_url'] = null;
        }

        $trackingDir = $this->trackingDataFolderSelector ?
            $this->trackingDataFolderSelector->retrieve() :
            TrackingDataFolderSelector::retrieveForLogsDir($this->logsDir);

        if (!is_dir($trackingDir)) {
            mkdir($trackingDir, 0777, true);
        }

        $settingsFile = $trackingDir . DIRECTORY_SEPARATOR . 'settings.ser';
        file_put_contents($settingsFile, serialize($configuration));
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getKeyName($key)
    {
        return $this->prefix . '.' . $key;
    }
}
