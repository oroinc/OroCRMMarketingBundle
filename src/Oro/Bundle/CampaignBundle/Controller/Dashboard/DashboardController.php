<?php

namespace Oro\Bundle\CampaignBundle\Controller\Dashboard;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    const CAMPAIGN_LEAD_COUNT          = 5;
    const CAMPAIGN_OPPORTUNITY_COUNT   = 5;
    const CAMPAIGN_CLOSE_REVENUE_COUNT = 5;

    /**
     * @Route(
     *      "/campaign_lead/chart/{widget}",
     *      name="oro_campaign_dashboard_campaigns_leads_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCampaignBundle:Dashboard:campaignLeads.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function campaignLeadsAction(Request $request, $widget)
    {
        $items                   = $this->get('oro_campaign.dashboard.campaign_data_provider')
            ->getCampaignLeadsData(
                $this->get('oro_dashboard.widget_configs')
                    ->getWidgetOptions($request->query->get('_widgetId', null))
                    ->get('dateRange')
            );
        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                [
                    'name'        => 'bar_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => ['field_name' => 'number']
                    ],
                    'settings'    => ['xNoTicks' => count($items)],
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/campaign_opportunity/chart/{widget}",
     *      name="oro_campaign_dashboard_campaigns_opportunity_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCampaignBundle:Dashboard:campaignOpportunity.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function campaignOpportunityAction(Request $request, $widget)
    {
        $items = $this->get('oro_campaign.dashboard.campaign_data_provider')
            ->getCampaignOpportunitiesData(
                $this->get('oro_dashboard.widget_configs')
                    ->getWidgetOptions($request->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                [
                    'name'        => 'bar_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => ['field_name' => 'number']
                    ],
                    'settings'    => ['xNoTicks' => count($items)],
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/campaign_by_close_revenue/chart/{widget}",
     *      name="oro_campaign_dashboard_campaigns_by_close_revenue_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCampaignBundle:Dashboard:campaignByCloseRevenue.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function campaignByCloseRevenueAction(Request $request, $widget)
    {
        $items = $this->get('oro_campaign.dashboard.campaign_data_provider')
            ->getCampaignsByCloseRevenueData(
                $this->get('oro_dashboard.widget_configs')
                    ->getWidgetOptions($request->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                [
                    'name'        => 'bar_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => ['field_name' => 'closeRevenue', 'formatter' => 'formatCurrency']
                    ],
                    'settings'    => ['xNoTicks' => count($items)],
                ]
            )
            ->getView();

        return $widgetAttr;
    }
}
