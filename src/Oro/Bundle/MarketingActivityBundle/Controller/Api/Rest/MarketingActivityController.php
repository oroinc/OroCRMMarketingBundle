<?php

namespace Oro\Bundle\MarketingActivityBundle\Controller\Api\Rest;

use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @Rest\RouteResource("marketingactivity")
 * @Rest\NamePrefix("oro_api_")
 */
class MarketingActivityController extends RestController implements ClassResourceInterface
{
    /**
     * Get filtered marketing activities for given entity
     *
     * @param string  $entityClass Entity class name
     * @param integer $entityId    Entity id
     *
     * @QueryParam(
     *     name="pageFilter", nullable=true,
     *     description="Array with pager filters, e.g. [first|last item date, array of ids with same date, action type]"
     * )
     * @QueryParam(
     *      name="filter", nullable=true,
     *      description="Array with filters values"
     * )
     *
     * @ApiDoc(
     *      description="Returns an array with collection of objects and count of all records",
     *      resource=true,
     *      statusCodes={
     *          200="Returned when successful",
     *      }
     * )
     * @return JsonResponse
     */
    public function cgetAction($entityClass, $entityId)
    {
        $entityClass = $this->get('oro_entity.routing_helper')->resolveEntityClass($entityClass);
        $filter      = $this->getRequest()->get('filter');
        $pageFilter  = $this->getRequest()->get('pageFilter');

         $queryBuilder = $this->getDoctrine()
            ->getRepository('OroMarketingActivityBundle:MarketingActivity')
            ->getMarketingActivitySectionItemsQueryBuilder($entityClass, $entityId, $pageFilter);

        $this->get('oro_marketing_activity.section_data.filter.helper')
            ->addFiltersToQuery($queryBuilder, $filter);

        $items = $queryBuilder->setMaxResults(MarketingActivity::MARKETING_ACTIVITY_SECTION_ITEMS_PER_PAGE)
            ->getQuery()
            ->getArrayResult();

        $results = $this->get('oro_marketing_activity.normalizer.marketing_activity.section_data')
            ->getNormalizedData($items);

        return new JsonResponse($results);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_marketing_activity.marketing_activity.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }
}
