<?php

namespace Oro\Bundle\MarketingListBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles api requests related to the marketing lists.
 *
 * @Rest\RouteResource("marketinglist")
 * @Rest\NamePrefix("oro_api_")
 */
class MarketingListController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @Rest\Delete(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete Marketing List",
     *      resource=true
     * )
     * @AclAncestor("oro_marketing_list_delete")
     *
     * @return Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @Rest\Get(
     *      "/marketinglist/contact-information/field/type"
     * )
     * @ApiDoc(
     *     description="Get contact information field type by field name",
     *     resource=true
     * )
     * @param Request $request
     * @return Response
     */
    public function contactInformationFieldTypeAction(Request $request)
    {
        $entity = $request->get('entity');
        $field = $request->get('field');
        /** @var ContactInformationFieldHelper $helper */
        $helper = $this->get('oro_marketing_list.contact_information_field_helper');
        return $this->handleView(
            $this->view(
                $helper->getContactInformationFieldType($entity, $field),
                Response::HTTP_OK
            )
        );
    }

    /**
     * @Rest\Get(
     *      "/marketinglist/contact-information/entity/fields"
     * )
     * @ApiDoc(
     *     description="Get entity contact information fields",
     *     resource=true
     * )
     * @param Request $request
     * @return Response
     */
    public function entityContactInformationFieldsAction(Request $request)
    {
        $entity = $request->get('entity');
        /** @var ContactInformationFieldHelper $helper */
        $helper = $this->get('oro_marketing_list.contact_information_field_helper');

        return $this->handleView(
            $this->view($helper->getEntityContactInformationFieldsInfo($entity), Response::HTTP_OK)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_marketing_list.marketing_list.manager.api');
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
