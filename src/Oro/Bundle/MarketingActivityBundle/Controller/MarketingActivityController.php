<?php

namespace Oro\Bundle\MarketingActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\FilterBundle\Filter\DateTimeRangeFilter;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/marketing-activity")
 */
class MarketingActivityController extends Controller
{
    /**
     * @param int $campaignId
     *
     * @return array
     *
     * @Route(
     *         "/widget/marketing-activities/summary/{campaignId}",
     *          name="oro_marketing_activity_widget_summary",
     *          requirements={"campaignId"="\d+"}
     * )
     * @AclAncestor("oro_campaign_view"))
     * @Template
     */
    public function summaryAction($campaignId, $entityClass, $entityId)
    {
        $summaryData = $this->getDoctrine()
            ->getRepository('OroMarketingActivityBundle:MarketingActivity')
            ->getMarketingActivitySummaryByCampaign($campaignId, $entityClass, $entityId);

        return [
            'summary' => $summaryData
        ];
    }

    /**
     * @Route(
     *     "/view/widget/marketing-activities/{entityClass}/{entityId}",
     *     name="oro_marketing_activity_widget_marketing_activities"
     * )
     * @Template("OroMarketingActivityBundle:MarketingActivity:marketingActivitiesSection.html.twig")
     *
     * @param string  $entityClass The entity class which marketing activities should be rendered
     * @param integer $entityId    The entity object id which marketing activities should be rendered
     *
     * @return array
     */
    public function widgetAction($entityClass, $entityId)
    {
        $entity = $this->get('oro_entity.routing_helper')->getEntity($entityClass, $entityId);

        /** @var DateTimeRangeFilter $dateRangeFilter */
        $dateRangeFilter = $this->get('oro_filter.datetime_range_filter');
        $campaignEntityClass = 'Oro\Bundle\CampaignBundle\Entity\Campaign';
        $template = "OroMarketingActivityBundle:MarketingActivity:js/marketingActivitySectionItem.html.twig";
        $configurationEntityKey = $this->get('oro_entity.routing_helper')->getUrlSafeClassName($campaignEntityClass);

        $entityClass = $this->get('oro_entity.routing_helper')->resolveEntityClass($entityClass);
        $marketingActivitySectionItems = $this->getDoctrine()
            ->getRepository('OroMarketingActivityBundle:MarketingActivity')
            ->getMarketingActivitySectionItemsQueryBuilder($entityClass, $entityId)
            ->getQuery()
            ->getArrayResult();

        $campaignFilterValues = $this->get('oro_marketing_activity.normalizer.marketing_activity.section_data')
            ->getCampaignFilterValues($marketingActivitySectionItems);

        return [
            'entity'                  => $entity,
            'configuration'           => [
                $configurationEntityKey => [
                    'label' => $this->get('translator')->trans('oro.campaign.entity_label'),
                    'template' => $template,
                    'routes' => [
                        'itemView' => 'oro_marketing_activity_widget_marketing_activities_info'
                    ],
                    'has_comments' => false,
                ]
            ],
            'dateRangeFilterMetadata' => $dateRangeFilter->getMetadata(),
            'campaignFilterValues' => $campaignFilterValues,
        ];
    }

    /**
     * @Route(
     *      "/widget/marketing-activities/info/{id}",
     *      name="oro_marketing_activity_widget_marketing_activities_info",
     *      requirements={"id"="\d+"},
     * )
     * @Template("OroMarketingActivityBundle:MarketingActivity/widget:marketingActivitySectionItemInfo.html.twig")
     */
    public function infoAction($id, Request $request)
    {
        return [
            'campaignId'  => $id,
            'entityClass' => $request->get('targetActivityClass'),
            'entityId'    => $request->get('targetActivityId')
        ];
    }
}
