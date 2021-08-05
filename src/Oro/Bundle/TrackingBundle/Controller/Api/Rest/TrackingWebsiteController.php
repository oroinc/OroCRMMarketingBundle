<?php

namespace Oro\Bundle\TrackingBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for TrackingWebsite entity.
 */
class TrackingWebsiteController extends RestController
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete website",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_tracking_website_delete",
     *      type="entity",
     *      class="OroTrackingBundle:TrackingWebsite",
     *      permission="DELETE"
     * )
     * @return Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_tracking.tracking_website.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_tracking.form.tracking_website');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_tracking.form.handler.tracking_website');
    }
}
