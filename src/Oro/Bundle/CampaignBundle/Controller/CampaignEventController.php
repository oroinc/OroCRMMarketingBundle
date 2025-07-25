<?php

namespace Oro\Bundle\CampaignBundle\Controller;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\DataGridBundle\Datagrid\ManagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Adds chart widget with campaign tracking detailed report grid
 */
#[Route(path: '/campaign/event')]
class CampaignEventController extends AbstractController
{
    const PRECALCULATED_SUFFIX = '-precalculated';

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            ManagerInterface::class,
            ChartViewBuilder::class,
            ConfigProvider::class
        ]);
    }

    /**
     * @param string $period
     * @param Campaign $campaign
     * @return array
     */
    #[Route(path: '/plot/{period}/{campaign}', name: 'oro_campaign_event_plot')]
    #[Template]
    #[AclAncestor('oro_campaign_view')]
    public function plotAction($period, Campaign $campaign)
    {
        $supportedPeriods = [
            Campaign::PERIOD_HOURLY,
            Campaign::PERIOD_DAILY,
            Campaign::PERIOD_MONTHLY,
        ];
        if (!in_array($period, $supportedPeriods)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Supported periods are: %s. %s given',
                    implode(', ', $supportedPeriods),
                    $period
                )
            );
        }

        $gridName = sprintf('campaign-tracking-detailed-report-%s-grid', $period);
        if ($period !== Campaign::PERIOD_HOURLY) {
            $gridName .= self::PRECALCULATED_SUFFIX;
        }

        $gridParameters = [
            'campaign' => $campaign,
            PagerInterface::PAGER_ROOT_PARAM => [
                PagerInterface::DISABLED_PARAM => true
            ]
        ];

        $datagrid = $this->container
            ->get(ManagerInterface::class)
            ->getDatagridByRequestParams(
                $gridName,
                $gridParameters
            );

        $chartName = 'campaign_line_chart';
        $chartView = $this->container
            ->get(ChartViewBuilder::class)
            ->setDataGrid($datagrid)
            ->setOptions(
                array_merge_recursive(
                    [
                        'name' => $chartName,
                        'default_settings' => [
                            'period' => $period
                        ]
                    ],
                    $this->container
                        ->get(ConfigProvider::class)
                        ->getChartConfig($chartName)
                )
            )
            ->getView();

        return [
            'chartView' => $chartView
        ];
    }
}
