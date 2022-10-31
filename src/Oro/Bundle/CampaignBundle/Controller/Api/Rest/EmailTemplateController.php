<?php

namespace Oro\Bundle\CampaignBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller for EmailTemplate entity.
 */
class EmailTemplateController extends RestController
{
    /**
     * REST GET email campaign templates by entity name
     *
     * @param int|null $id
     *
     * @ApiDoc(
     *     description="Get email campaign templates by entity name",
     *     resource=true
     * )
     * @AclAncestor("oro_email_emailtemplate_index")
     *
     * @return Response
     */
    public function cgetAction(int $id = null)
    {
        if (!$id) {
            return $this->handleView(
                $this->view(null, Response::HTTP_NOT_FOUND)
            );
        }

        $marketingList = $this
            ->getDoctrine()
            ->getRepository('OroMarketingListBundle:MarketingList')
            ->find((int)$id);

        $organization = $this->get('oro_security.token_accessor')->getOrganization();

        if (!$marketingList || !$organization) {
            return $this->handleView(
                $this->view(null, Response::HTTP_NOT_FOUND)
            );
        }

        $request = $this->get('request_stack')->getMainRequest();
        $includeNonEntity = (bool)$request?->get('includeNonEntity', false);
        $includeSystemTpl = (bool)$request?->get('includeSystemTemplates', true);

        $templatesQb = $this
            ->getDoctrine()
            ->getRepository('OroEmailBundle:EmailTemplate')
            ->getEntityTemplatesQueryBuilder(
                $marketingList->getEntity(),
                $organization,
                $includeNonEntity,
                $includeSystemTpl
            );

        $templates = $templatesQb->getQuery()->getArrayResult();
        return $this->handleView(
            $this->view($templates, Response::HTTP_OK)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
