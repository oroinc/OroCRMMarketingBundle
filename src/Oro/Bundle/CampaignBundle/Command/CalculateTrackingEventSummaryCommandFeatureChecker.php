<?php

namespace Oro\Bundle\CampaignBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandFeatureCheckerInterface;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;

/**
 * Allows to execute "oro:cron:calculate-tracking-event-summary" command
 * when either "tracking" or "campaign" feature is enabled.
 */
class CalculateTrackingEventSummaryCommandFeatureChecker implements CronCommandFeatureCheckerInterface
{
    private CronCommandFeatureCheckerInterface $innerChecker;
    private FeatureChecker $featureChecker;

    public function __construct(CronCommandFeatureCheckerInterface $innerChecker, FeatureChecker $featureChecker)
    {
        $this->innerChecker = $innerChecker;
        $this->featureChecker = $featureChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function isFeatureEnabled(string $commandName): bool
    {
        if ('oro:cron:calculate-tracking-event-summary' === $commandName) {
            return
                $this->featureChecker->isFeatureEnabled('tracking')
                || $this->featureChecker->isFeatureEnabled('campaign');
        }

        return $this->innerChecker->isFeatureEnabled($commandName);
    }
}
