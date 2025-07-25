<?php

namespace Oro\Bundle\MarketingActivityBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Oro\Bundle\MarketingActivityBundle\Entity\Repository\MarketingActivityRepository;
use Oro\Bundle\MarketingActivityBundle\Filter\MarketingActivitiesSectionFilterHelper;
use Oro\Bundle\MarketingActivityBundle\Provider\MarketingActivitySectionDataNormalizer;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Provides various marketing activity widgets.
 */
#[Route(path: '/marketing-activity')]
class MarketingActivityController extends AbstractController
{
    /**
     *
     * @param integer $campaignId  The ID of Campaign entity
     * @param string  $entityClass Entity class name
     * @param integer $entityId    Entity id
     * @return array
     */
    #[Route(
        path: '/widget/marketing-activities/summary/{campaignId}',
        name: 'oro_marketing_activity_widget_summary',
        requirements: ['campaignId' => '\d+']
    )]
    #[Template]
    #[AclAncestor('oro_marketing_activity_view')]
    public function summaryAction($campaignId, $entityClass = null, $entityId = null)
    {
        $summaryData = $this->container->get('doctrine')
            ->getRepository(MarketingActivity::class)
            ->getMarketingActivitySummaryByCampaign($campaignId, $entityClass, $entityId);

        return [
            'summary' => $summaryData
        ];
    }

    /**
     *
     * @param string  $entityClass The entity class which marketing activities should be rendered
     * @param integer $entityId    The entity object id which marketing activities should be rendered
     * @return array
     */
    #[Route(
        path: '/view/widget/marketing-activities/{entityClass}/{entityId}',
        name: 'oro_marketing_activity_widget_marketing_activities'
    )]
    #[Template('@OroMarketingActivity/MarketingActivity/marketingActivitiesSection.html.twig')]
    #[AclAncestor('oro_marketing_activity_view')]
    public function widgetAction($entityClass, $entityId)
    {
        $routingHelper = $this->container->get(EntityRoutingHelper::class);
        $entity = $routingHelper->getEntity($entityClass, $entityId);

        $campaignEntityClass = Campaign::class;
        $configurationEntityKey = $routingHelper->getUrlSafeClassName($campaignEntityClass);

        $entityClass = $routingHelper->resolveEntityClass($entityClass);
        $marketingActivitySectionItems = $this->container->get('doctrine')
            ->getRepository(MarketingActivity::class)
            ->getMarketingActivitySectionItemsQueryBuilder($entityClass, $entityId)
            ->getQuery()
            ->getArrayResult();

        $campaignFilterValues = $this->container->get(MarketingActivitySectionDataNormalizer::class)
            ->getCampaignFilterValues($marketingActivitySectionItems);

        return [
            'entity'                  => $entity,
            'configurationKey'        => $configurationEntityKey,
            'campaignFilterValues'    => $campaignFilterValues,
        ];
    }

    /**
     *
     * @param integer $id The ID of Campaign entity
     * @param Request $request
     * @return array
     */
    #[Route(
        path: '/widget/marketing-activities/info/{id}',
        name: 'oro_marketing_activity_widget_marketing_activities_info',
        requirements: ['id' => '\d+']
    )]
    #[Template('@OroMarketingActivity/MarketingActivity/widget/marketingActivitySectionItemInfo.html.twig')]
    #[AclAncestor('oro_marketing_activity_view')]
    public function infoAction($id, Request $request)
    {
        return [
            'campaignId'  => $id,
            'entityClass' => $request->get('targetActivityClass'),
            'entityId'    => $request->get('targetActivityId')
        ];
    }

    /**
     * Get filtered marketing activities for given entity
     *
     *
     * @param string  $entityClass The entity class which marketing activities should be rendered
     * @param integer $entityId    The entity object id which marketing activities should be rendered
     * @param Request $request
     * @return JsonResponse
     */
    #[Route(
        path: '/view/widget/marketing-activities/list/{entityClass}/{entityId}',
        name: 'oro_marketing_activity_widget_marketing_activities_list'
    )]
    #[AclAncestor('oro_marketing_activity_view')]
    public function listAction($entityClass, $entityId, Request $request)
    {
        $entityClass = $this->container->get(EntityRoutingHelper::class)->resolveEntityClass($entityClass);
        $filter      = $request->get('filter');
        $pageFilter  = $request->get('pageFilter');
        /** @var MarketingActivityRepository $repository */
        $repository = $this->container->get('doctrine')->getRepository(MarketingActivity::class);

        $queryBuilder = $repository
            ->getMarketingActivitySectionItemsQueryBuilder($entityClass, $entityId, $pageFilter);

        $this->container->get(MarketingActivitiesSectionFilterHelper::class)
            ->addFiltersToQuery($queryBuilder, $filter);

        $items = $queryBuilder->setMaxResults(
            $this->container->get(ConfigManager::class)->get('oro_activity_list.per_page')
        )
            ->getQuery()
            ->getArrayResult();

        $repository->addEventTypeData($items, $entityClass, $entityId);

        $results = $this->container->get(MarketingActivitySectionDataNormalizer::class)
            ->getNormalizedData($items, $entityClass, $entityId);

        return new JsonResponse($results);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EntityRoutingHelper::class,
                MarketingActivitySectionDataNormalizer::class,
                MarketingActivitiesSectionFilterHelper::class,
                ConfigManager::class,
                'doctrine' => ManagerRegistry::class,
            ]
        );
    }
}
