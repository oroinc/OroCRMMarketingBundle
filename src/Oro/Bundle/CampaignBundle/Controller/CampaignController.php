<?php

namespace Oro\Bundle\CampaignBundle\Controller;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for marketing campaigns.
 *
 * @Route("/campaign")
 */
class CampaignController extends AbstractController
{
    /**
     * @Route("/", name="oro_campaign_index")
     * @AclAncestor("oro_campaign_view")
     * @Template
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Campaign::class
        ];
    }

    /**
     * Create campaign
     *
     * @Route("/create", name="oro_campaign_create")
     * @Template("@OroCampaign/Campaign/update.html.twig")
     * @Acl(
     *      id="oro_campaign_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCampaignBundle:Campaign"
     * )
     */
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new Campaign());
    }

    /**
     * Edit campaign
     *
     * @Route("/update/{id}", name="oro_campaign_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_campaign_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCampaignBundle:Campaign"
     * )
     */
    public function updateAction(Campaign $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    /**
     * View campaign
     *
     * @Route("/view/{id}", name="oro_campaign_view")
     * @Acl(
     *      id="oro_campaign_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCampaignBundle:Campaign"
     * )
     * @Template
     */
    public function viewAction(Campaign $entity): array
    {
        $codesHistory = $this->getDoctrine()
            ->getRepository("OroCampaignBundle:Campaign")
            ->getCodesHistory($entity);

        return [
            'entity' => $entity,
            'codes_history' => $codesHistory
        ];
    }

    /**
     * Process save campaign entity
     */
    protected function update(Campaign $entity): array|RedirectResponse
    {
        return $this->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->get('oro_campaign.campaign.form'),
            $this->get(TranslatorInterface::class)->trans('oro.campaign.controller.campaign.saved.message')
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                'oro_campaign.campaign.form' => Form::class,
                UpdateHandlerFacade::class
            ]
        );
    }
}
