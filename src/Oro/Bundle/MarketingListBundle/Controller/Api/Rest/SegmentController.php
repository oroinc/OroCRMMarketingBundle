<?php

namespace Oro\Bundle\MarketingListBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for Segment entity.
 */
class SegmentController extends RestController
{
    /**
     * @param int $id
     *
     * @ApiDoc(
     *      description="Run Static Marketing List Segment",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_marketing_list_update')]
    public function postRunAction(int $id)
    {
        /** @var Segment $segment */
        $segment = $this->getManager()->find($id);
        if (!$segment) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }

        try {
            $this->container->get('oro_segment.static_segment_manager')->run($segment);
            return $this->handleView($this->view(null, Response::HTTP_NO_CONTENT));
        } catch (\LogicException $e) {
            return $this->handleView($this->view(null, Response::HTTP_BAD_REQUEST));
        }
    }

    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_segment.segment_manager.api');
    }

    /**
     *
     * @throws \BadMethodCallException
     */
    #[\Override]
    public function getForm()
    {
        throw new \BadMethodCallException('This method is not implemented yet.');
    }

    /**
     *
     * @throws \BadMethodCallException
     */
    #[\Override]
    public function getFormHandler()
    {
        throw new \BadMethodCallException('This method is not implemented yet.');
    }
}
