<?php

namespace Oro\Bundle\MarketingActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
    public function summaryAction($campaignId)
    {
        return $this->getDoctrine()
            ->getRepository('OroMarketingActivityBundle:MarketingActivity')
            ->getMarketingActivitySummaryByCampaign($campaignId);
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

        return [
            'entity'                  => $entity,
            'configuration'           => ['Oro_Bundle_MarketingActivityBundle_Entity_MarketingActivity' => [
                'label' => "Marketing Activity",
                'template' => "OroMarketingActivityBundle:MarketingActivity:js/marketingActivitySectionItem.html.twig",
                'routes' => ['itemView' => 'oro_marketing_activity_widget_marketing_activities_info'],
                'has_comments' => false,
            ]],
            'dateRangeFilterMetadata' => $dateRangeFilter->getMetadata(),
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
    public function infoAction(MarketingActivity $entity)
    {
        return [
            'entity'         => $entity,
        ];
    }
}
