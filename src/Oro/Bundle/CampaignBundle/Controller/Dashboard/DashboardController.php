<?php

namespace Oro\Bundle\CampaignBundle\Controller\Dashboard;

use Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Adds charts for campaign leads, campaign opportunity, campaign by close revenue
 */
class DashboardController extends AbstractController
{
    const CAMPAIGN_LEAD_COUNT          = 5;
    const CAMPAIGN_OPPORTUNITY_COUNT   = 5;
    const CAMPAIGN_CLOSE_REVENUE_COUNT = 5;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            CampaignDataProvider::class,
            WidgetConfigs::class,
            ChartViewBuilder::class,
        ]);
    }

    /**
     * @Route(
     *      "/campaign_lead/chart/{widget}",
     *      name="oro_campaign_dashboard_campaigns_leads_chart",
     *      requirements={"widget"="[\w\-]+"}
     * )
     * @Template("OroCampaignBundle:Dashboard:campaignLeads.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function campaignLeadsAction(Request $request, $widget)
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $items = $this->get(CampaignDataProvider::class)
            ->getCampaignLeadsData(
                $widgetConfigs
                    ->getWidgetOptions($request->query->get('_widgetId', null))
                    ->get('dateRange')
            );
        $widgetAttr              = $widgetConfigs->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
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
     *      requirements={"widget"="[\w\-]+"}
     * )
     * @Template("OroCampaignBundle:Dashboard:campaignOpportunity.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function campaignOpportunityAction(Request $request, $widget)
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $items = $this->get(CampaignDataProvider::class)
            ->getCampaignOpportunitiesData(
                $widgetConfigs
                    ->getWidgetOptions($request->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr              = $widgetConfigs->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
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
     *      requirements={"widget"="[\w\-]+"}
     * )
     * @Template("OroCampaignBundle:Dashboard:campaignByCloseRevenue.html.twig")
     * @param Request $request
     * @param mixed $widget
     * @return array
     */
    public function campaignByCloseRevenueAction(Request $request, $widget)
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $items = $this->get(CampaignDataProvider::class)
            ->getCampaignsByCloseRevenueData(
                $widgetConfigs
                    ->getWidgetOptions($request->query->get('_widgetId', null))
                    ->get('dateRange')
            );

        $widgetAttr              = $widgetConfigs->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
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
