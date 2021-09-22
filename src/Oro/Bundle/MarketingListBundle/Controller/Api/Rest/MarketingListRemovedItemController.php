<?php

namespace Oro\Bundle\MarketingListBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListRemovedItem;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST API controller for marketing list removed items.
 */
class MarketingListRemovedItemController extends RestController
{
    /**
     * REST POST
     *
     * @ApiDoc(
     *     description="Create new MarketingListRemovedItem",
     *     resource=true
     * )
     * @AclAncestor("oro_marketing_list_removed_item_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete MarketingListRemovedItem",
     *     resource=true
     * )
     * @AclAncestor("oro_marketing_list_removed_item_delete")
     *
     * @return Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Remove marketing list entity item from list
     *
     * Returns
     * - HTTP_OK (200)
     *
     * @ApiDoc(description="Remove marketing list entity item", resource=true)
     * @AclAncestor("oro_marketing_list_removed_item_delete")
     *
     * @param MarketingList $marketingList
     * @param int           $id
     *
     * @return Response
     */
    public function removeAction(MarketingList $marketingList, int $id)
    {
        $item = new MarketingListRemovedItem();
        $item
            ->setMarketingList($marketingList)
            ->setEntityId($id);

        $violations = $this->get('validator')->validate($item);
        if ($violations->count()) {
            return $this->handleView($this->view($violations, Response::HTTP_BAD_REQUEST));
        }

        $em = $this->getManager()->getObjectManager();
        $em->persist($item);
        $em->flush($item);

        $entityName = (string) $this
            ->get('oro_entity_config.provider.entity')
            ->getConfig($marketingList->getEntity())
            ->get('label');

        return $this->handleView(
            $this->view(
                array(
                    'successful' => true,
                    'message'    => $this->get('translator')->trans(
                        'oro.marketinglist.controller.removed',
                        ['%entityName%' => $this->get('translator')->trans($entityName)]
                    )
                ),
                Response::HTTP_OK
            )
        );
    }

    /**
     * @param MarketingList $marketingList
     * @param int           $id
     *
     * @ApiDoc(
     *     description="Delete MarketingListRemovedItem by marketing list entity",
     *     resource=true
     * )
     * @AclAncestor("oro_marketing_list_removed_item_delete")
     *
     * @return Response
     */
    public function unremoveAction(MarketingList $marketingList, $id)
    {
        /** @var MarketingListRemovedItem[] $forRemove */
        $forRemove = $this->getManager()->getRepository()->findBy(
            array(
                'marketingList' => $marketingList,
                'entityId'      => $id
            )
        );

        if ($forRemove) {
            try {
                $item = $forRemove[0];
                $this->getDeleteHandler()->handleDelete($item->getId(), $this->getManager());
            } catch (EntityNotFoundException $e) {
                return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
            } catch (AccessDeniedException $e) {
                return $this->handleView($this->view(['reason' => $e->getMessage()], Response::HTTP_FORBIDDEN));
            }
        }

        $entityName = (string) $this
            ->get('oro_entity_config.provider.entity')
            ->getConfig($marketingList->getEntity())
            ->get('label');

        return $this->handleView(
            $this->view(
                array(
                    'successful' => true,
                    'message'    => $this->get('translator')->trans(
                        'oro.marketinglist.controller.unremoved',
                        ['%entityName%' => $this->get('translator')->trans($entityName)]
                    )
                ),
                Response::HTTP_OK
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_marketing_list.marketing_list_removed_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_marketing_list.form.marketing_list_removed_item');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_marketing_list.form.handler.marketing_list_removed_item');
    }
}
