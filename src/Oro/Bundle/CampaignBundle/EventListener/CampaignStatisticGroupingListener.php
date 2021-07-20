<?php

namespace Oro\Bundle\CampaignBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\MarketingListBundle\Model\MarketingListHelper;
use Oro\Bundle\QueryDesignerBundle\Model\GroupByHelper;

/**
 * Adds fields that are not mentioned in aggregate functions to GROUP BY for email campaign datagrids.
 */
class CampaignStatisticGroupingListener
{
    const MIXIN_NAME = 'orocrm-email-campaign-marketing-list-items-mixin';
    const MANUAL_MIXIN_NAME = 'orocrm-email-campaign-marketing-list-manual-items-mixin';

    /**
     * @var MarketingListHelper
     */
    protected $marketingListHelper;

    /**
     * @var GroupByHelper
     */
    protected $groupByHelper;

    public function __construct(MarketingListHelper $marketingListHelper, GroupByHelper $groupByHelper)
    {
        $this->marketingListHelper = $marketingListHelper;
        $this->groupByHelper = $groupByHelper;
    }

    /**
     * Add fields that are not mentioned in aggregate functions to GROUP BY.
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();
        $parameters = $event->getParameters();

        if (!$this->isApplicable($config->getName(), $parameters)) {
            return;
        }

        $query = $config->getOrmQuery();
        $groupBy = $this->groupByHelper->getGroupByFields($query->getGroupBy(), $query->getSelect());
        if ($groupBy) {
            $query->setGroupBy(implode(',', $groupBy));
        }
    }

    /**
     * This listener is applicable for marketing list grids that has emailCampaign parameter set.
     *
     * @param string $gridName
     * @param ParameterBag $parameterBag
     *
     * @return bool
     */
    public function isApplicable($gridName, ParameterBag $parameterBag)
    {
        if (!$parameterBag->has('emailCampaign')) {
            return false;
        }

        return (bool)$this->marketingListHelper->getMarketingListIdByGridName($gridName);
    }
}
