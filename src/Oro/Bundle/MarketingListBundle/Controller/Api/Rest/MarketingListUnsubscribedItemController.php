<?php

namespace Oro\Bundle\MarketingListBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListUnsubscribedItem;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * REST API controller for marketing list unsubscribed item.
 */
class MarketingListUnsubscribedItemController extends RestController
{
    /**
     * REST POST
     *
     * @ApiDoc(
     *     description="Create new MarketingListUnsubscribedItem",
     *     resource=true
     * )
     * @AclAncestor("oro_marketinglist_unsubscribed_item_create")
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
     * @AclAncestor("oro_marketinglist_unsubscribed_item_delete")
     *
     * @return Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Unsubscribe marketing list entity item
     *
     * Returns
     * - HTTP_OK (200)
     *
     * @ApiDoc(description="Unsubscribe marketing list entity item", resource=true)
     * @AclAncestor("oro_marketinglist_unsubscribed_item_create")
     *
     * @param MarketingList $marketingList
     * @param int           $id
     *
     * @return Response
     */
    public function unsubscribeAction(MarketingList $marketingList, int $id)
    {
        $item = new MarketingListUnsubscribedItem();
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
                        'oro.marketinglist.controller.unsubscribed',
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
     *     description="Delete MarketingListUnsubscribedItem by marketing list entity",
     *     resource=true
     * )
     * @AclAncestor("oro_marketinglist_unsubscribed_item_delete")
     *
     * @return Response
     */
    public function subscribeAction(MarketingList $marketingList, int $id)
    {
        /** @var MarketingListUnsubscribedItem[] $forRemove */
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
                        'oro.marketinglist.controller.subscribed',
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
        return $this->get('oro_marketing_list.marketing_list_unsubscribed_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_marketing_list.form.marketing_list_unsubscribed_item');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_marketing_list.form.handler.marketing_list_unsubscribed_item');
    }
}
