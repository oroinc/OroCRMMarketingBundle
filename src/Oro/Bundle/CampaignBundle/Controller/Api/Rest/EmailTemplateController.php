<?php

namespace Oro\Bundle\CampaignBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * API controller for EmailTemplate entity.
 *
 * @RouteResource("emailcampaign_email_template")
 * @NamePrefix("oro_api_")
 */
class EmailTemplateController extends RestController
{
    /**
     * REST GET email campaign templates by entity name
     *
     * @param int|null $id
     *
     * @Rest\Get(requirements={"id"="\d*"})
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

        if (!$marketingList) {
            return $this->handleView(
                $this->view(null, Response::HTTP_NOT_FOUND)
            );
        }

        $organization = $this->get('oro_security.token_accessor')->getOrganization();

        $templatesQueryBuilder = $this
            ->getDoctrine()
            ->getRepository('OroEmailBundle:EmailTemplate')
            ->getEntityTemplatesQueryBuilder($marketingList->getEntity(), $organization);

        $templates = $templatesQueryBuilder->getQuery()->getArrayResult();
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
