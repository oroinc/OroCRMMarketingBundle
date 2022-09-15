<?php

namespace Oro\Bundle\CampaignBundle\Controller\Dashboard;

use Oro\Bundle\CampaignBundle\Dashboard\CampaignDataProvider;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Exception\InvalidConfigurationException;
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
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
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
     * @Template("@OroCampaign/Dashboard/campaignLeads.html.twig")
     * @param Request $request
     * @param string $widget
     * @return array
     * @throws InvalidConfigurationException
     */
    public function campaignLeadsAction(Request $request, string $widget): array
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $widgetOptions = $widgetConfigs->getWidgetOptions($request->query->get('_widgetId', null));
        $dateRange = $widgetOptions->get('dateRange');
        $maxResults = $widgetOptions->get('maxResults') ?? CampaignDataProvider::CAMPAIGN_LEAD_COUNT;
        $hideCampaign = $widgetOptions->get('hideCampaign') ?? true;

        $items = $this->get(CampaignDataProvider::class)->getCampaignLeadsData($dateRange, $hideCampaign, $maxResults);

        /** @var array $widgetAttr */
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

        if (!isset($widgetAttr['widgetConfiguration']['hideCampaign']['value'])) {
            // set default value to show on widget.
            $widgetAttr['widgetConfiguration']['hideCampaign']['value'] = $hideCampaign;
        }

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/campaign_opportunity/chart/{widget}",
     *      name="oro_campaign_dashboard_campaigns_opportunity_chart",
     *      requirements={"widget"="[\w\-]+"}
     * )
     * @Template("@OroCampaign/Dashboard/campaignOpportunity.html.twig")
     * @param Request $request
     * @param string $widget
     * @return array
     * @throws InvalidConfigurationException
     */
    public function campaignOpportunityAction(Request $request, string $widget): array
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $widgetOptions = $widgetConfigs->getWidgetOptions($request->query->get('_widgetId', null));
        $dateRange = $widgetOptions->get('dateRange');
        $maxResults = $widgetOptions->get('maxResults') ?? CampaignDataProvider::CAMPAIGN_OPPORTUNITY_COUNT;

        $items = $this->get(CampaignDataProvider::class)->getCampaignOpportunitiesData($dateRange, $maxResults);

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
     * @Template("@OroCampaign/Dashboard/campaignByCloseRevenue.html.twig")
     * @param Request $request
     * @param string $widget
     * @return array
     * @throws InvalidConfigurationException
     */
    public function campaignByCloseRevenueAction(Request $request, string $widget): array
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $widgetOptions = $widgetConfigs->getWidgetOptions($request->query->get('_widgetId', null));
        $dateRange = $widgetOptions->get('dateRange');
        $maxResults = $widgetOptions->get('maxResults') ?? CampaignDataProvider::CAMPAIGN_CLOSE_REVENUE_COUNT;

        $items = $this->get(CampaignDataProvider::class)->getCampaignsByCloseRevenueData($dateRange, $maxResults);

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
