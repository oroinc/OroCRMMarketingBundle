<?php

namespace Oro\Bundle\MarketingActivityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     *         "/widget/marketing-activity/summary/{campaignId}",
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
}
