<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Behat\Context;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

/**
 * This context save behat execution time, all detailed steps can be found at
 * - "Manage MarketingList Feature"
 */
class MarketingListFeatureToggleContext extends OroFeatureContext
{
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @When /^(?:|I )enable Marketing List feature$/
     */
    public function enableMarketingListFeature()
    {
        $this->setFeatureState(1, 'oro_marketing_list', 'feature_enabled');
    }

    /**
     * @When /^(?:|I )disable Marketing List feature$/
     */
    public function disableMarketingListFeature()
    {
        $this->setFeatureState(0, 'oro_marketing_list', 'feature_enabled');
    }

    /**
     * @param mixed $state
     * @param string $section
     * @param string $name
     */
    protected function setFeatureState($state, $section, $name)
    {
        $this->configManager->set(sprintf('%s.%s', $section, $name), $state ? 1 : 0);
        $this->configManager->flush();
    }
}
