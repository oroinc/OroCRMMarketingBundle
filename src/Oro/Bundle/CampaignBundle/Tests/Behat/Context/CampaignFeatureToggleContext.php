<?php

namespace Oro\Bundle\CampaignBundle\Tests\Behat\Context;

use Oro\Bundle\TestFrameworkBundle\Behat\Context\OroFeatureContext;

/**
 * This context save behat execution time, all detailed steps can be found at
 * - "Manage Campaign Feature"
 */
class CampaignFeatureToggleContext extends OroFeatureContext
{
    /**
     * @When /^(?:|I )enable Campaign feature$/
     */
    public function enableCampaignFeature()
    {
        $this->setFeatureState(1, 'oro_campaign', 'feature_enabled');
    }

    /**
     * @When /^(?:|I )disable Campaign feature$/
     */
    public function disableCampaignFeature()
    {
        $this->setFeatureState(0, 'oro_campaign', 'feature_enabled');
    }

    /**
     * @param mixed $state
     * @param string $section
     * @param string $name
     */
    protected function setFeatureState($state, $section, $name)
    {
        $configManager = $this->getAppContainer()->get('oro_config.global');
        $configManager->set(sprintf('%s.%s', $section, $name), $state ? 1 : 0);
        $configManager->flush();
    }
}
