<?php

namespace Oro\Bundle\MarketingListBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller for marketing lists.
 */
class MarketingListController extends RestController
{
    /**
     * REST DELETE
     *
     * @param int $id
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
        $fieldType = $helper->getContactInformationFieldType($entity, $field);

        return $this->handleView(
            $this->view(
                $fieldType,
                $fieldType ? Response::HTTP_OK : Response::HTTP_NO_CONTENT
            )
        );
    }

    /**
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
