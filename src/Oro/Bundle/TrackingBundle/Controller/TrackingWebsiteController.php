<?php

namespace Oro\Bundle\TrackingBundle\Controller;

use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Form\Type\TrackingWebsiteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for tracking websites.
 *
 * @Route("/tracking/website")
 */
class TrackingWebsiteController extends AbstractController
{
    /**
     * @Route(
     *      ".{_format}",
     *      name="oro_tracking_website_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="oro_tracking_website_view",
     *      type="entity",
     *      class="OroTrackingBundle:TrackingWebsite",
     *      permission="VIEW"
     * )
     * @Template
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => TrackingWebsite::class
        ];
    }

    /**
     * @Route("/create", name="oro_tracking_website_create")
     * @Acl(
     *      id="oro_tracking_website_create",
     *      type="entity",
     *      class="OroTrackingBundle:TrackingWebsite",
     *      permission="CREATE"
     * )
     * @Template("@OroTracking/TrackingWebsite/update.html.twig")
     */
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new TrackingWebsite());
    }

    /**
     * @Route("/update/{id}", name="oro_tracking_website_update", requirements={"id"="\d+"})
     * @Acl(
     *      id="oro_tracking_website_update",
     *      type="entity",
     *      class="OroTrackingBundle:TrackingWebsite",
     *      permission="EDIT"
     * )
     * @Template()
     */
    public function updateAction(TrackingWebsite $trackingWebsite): array|RedirectResponse
    {
        return $this->update($trackingWebsite);
    }

    /**
     * @Route("/view/{id}", name="oro_tracking_website_view", requirements={"id"="\d+"})
     * @AclAncestor("oro_tracking_website_view")
     * @Template()
     */
    public function viewAction(TrackingWebsite $trackingWebsite): array
    {
        return [
            'entity' => $trackingWebsite
        ];
    }

    protected function update(TrackingWebsite $trackingWebsite): array|RedirectResponse
    {
        return $this->get(UpdateHandlerFacade::class)->update(
            $trackingWebsite,
            $this->createForm(TrackingWebsiteType::class),
            $this->getTranslator()->trans('oro.tracking.trackingwebsite.saved_message')
        );
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->get(TranslatorInterface::class);
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
                UpdateHandlerFacade::class
            ]
        );
    }
}
