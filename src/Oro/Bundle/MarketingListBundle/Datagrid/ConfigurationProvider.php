<?php

namespace Oro\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\SegmentBundle\Entity\Segment;

/**
 * The provider for configuration of datagrids for marketing lists.
 */
class ConfigurationProvider implements ConfigurationProviderInterface
{
    public const GRID_PREFIX = 'oro_marketing_list_items_grid_';

    /**
     * @var ConfigurationProviderInterface
     */
    protected $chainConfigurationProvider;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var MarketingListHelper
     */
    protected $helper;

    /**
     * @var DatagridConfiguration[]
     */
    private $configuration = [];

    public function __construct(
        ConfigurationProviderInterface $chainConfigurationProvider,
        ConfigProvider $configProvider,
        MarketingListHelper $helper
    ) {
        $this->chainConfigurationProvider = $chainConfigurationProvider;
        $this->configProvider = $configProvider;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(string $gridName): bool
    {
        return (bool)$this->helper->getMarketingListIdByGridName($gridName);
    }

    /**
     * Get grid configuration based on marketing list type.
     *
     * Get segments or concrete entity grid configuration by marketing list type and entity.
     * This configuration will be used as marketing list items grid configuration.
     */
    public function getConfiguration(string $gridName): DatagridConfiguration
    {
        $marketingListId = $this->helper->getMarketingListIdByGridName($gridName);
        if (!$marketingListId) {
            throw new \RuntimeException(
                sprintf('Marketing List id not found in "%s" gridName.', $gridName)
            );
        }

        if (empty($this->configuration[$gridName])) {
            $marketingList = $this->helper->getMarketingList($marketingListId);
            if (!$marketingList) {
                throw new \RuntimeException(
                    sprintf('Marketing List with id "%s" not found.', $marketingListId)
                );
            }

            // Get configuration based on marketing list type
            if ($marketingList->isManual()) {
                $concreteGridName = $this->getEntityGridName($marketingList->getEntity());
            } else {
                $postfix = str_replace(self::GRID_PREFIX . $marketingList->getId(), '', $gridName);
                $concreteGridName = Segment::GRID_PREFIX . $marketingList->getSegment()->getId() . $postfix;
            }

            $concreteGridConfiguration =  $this->chainConfigurationProvider->getConfiguration($concreteGridName);
            // Reset configured name to current gridName for further usage in Listener and Extension
            $concreteGridConfiguration->setName($gridName);
            $this->configuration[$gridName] = $concreteGridConfiguration;
        }

        return $this->configuration[$gridName];
    }

    /**
     * @param string $entityName
     * @return string|null
     */
    protected function getEntityGridName($entityName)
    {
        $gridName = null;
        if ($this->configProvider->hasConfig($entityName)) {
            $config = $this->configProvider->getConfig($entityName);
            $gridName = $config->get('grid_name');
        }

        if (!$gridName) {
            throw new \RuntimeException(sprintf('Grid not found for entity "%s"', $entityName));
        }

        return $gridName;
    }
}
