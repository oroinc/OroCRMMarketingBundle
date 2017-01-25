<?php

namespace Oro\Bundle\MarketingActivityBundle\Controller\Api\Rest;

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

        $results = [
            'count' => 2,
            'data' =>[
                [
                    'id' => 1,
                    'campaignName' => 'Campaign 1',
                    'eventType' => 'Open',
                    'eventDate' => '2017-01-14T15:30:20+00:00',
                    'relatedActivityClass' => 'Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity',
                    'relatedActivityId' => 1,
                    'createdAt' => '2017-01-16T15:30:20+00:00',
                    'updatedAt' => '2017-01-16T15:30:20+00:00',
                    'editable' => false,
                    'removable' => false,
                ],
                [
                    'id' => 2,
                    'campaignName' => 'Campaign 2',
                    'eventType' => 'Forward',
                    'eventDate' => '2017-01-15T09:30:20+00:00',
                    'relatedActivityClass' => 'Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity',
                    'relatedActivityId' => 2,
                    'createdAt' => '2017-01-14T15:30:20+00:00',
                    'updatedAt' => '2017-01-14T15:30:20+00:00',
                    'editable' => false,
                    'removable' => false,
                ],
            ]
        ];

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
