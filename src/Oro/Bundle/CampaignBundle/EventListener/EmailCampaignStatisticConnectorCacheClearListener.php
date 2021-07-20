<?php

namespace Oro\Bundle\CampaignBundle\EventListener;

use Oro\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;

/**
 * Clears the marketing list item cache when the entity manager is cleared.
 */
class EmailCampaignStatisticConnectorCacheClearListener
{
    /** @var EmailCampaignStatisticsConnector */
    private $emailCampaignStatisticsConnector;

    public function __construct(EmailCampaignStatisticsConnector $emailCampaignStatisticsConnector)
    {
        $this->emailCampaignStatisticsConnector = $emailCampaignStatisticsConnector;
    }

    public function onClear()
    {
        $this->emailCampaignStatisticsConnector->clearMarketingListItemCache();
    }
}
