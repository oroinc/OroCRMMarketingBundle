<?php

namespace Oro\Bundle\TrackingBundle\Controller;

use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Form\Type\TrackingWebsiteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for tracking websites.
 */
#[Route(path: '/tracking/website')]
class TrackingWebsiteController extends AbstractController
{
    #[Route(
        path: '.{_format}',
        name: 'oro_tracking_website_index',
        requirements: ['_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template]
    #[Acl(id: 'oro_tracking_website_view', type: 'entity', class: TrackingWebsite::class, permission: 'VIEW')]
    public function indexAction(): array
    {
        return [
            'entity_class' => TrackingWebsite::class
        ];
    }

    #[Route(path: '/create', name: 'oro_tracking_website_create')]
    #[Template('@OroTracking/TrackingWebsite/update.html.twig')]
    #[Acl(id: 'oro_tracking_website_create', type: 'entity', class: TrackingWebsite::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new TrackingWebsite());
    }

    #[Route(path: '/update/{id}', name: 'oro_tracking_website_update', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_tracking_website_update', type: 'entity', class: TrackingWebsite::class, permission: 'EDIT')]
    public function updateAction(TrackingWebsite $trackingWebsite): array|RedirectResponse
    {
        return $this->update($trackingWebsite);
    }

    #[Route(path: '/view/{id}', name: 'oro_tracking_website_view', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_tracking_website_view')]
    public function viewAction(TrackingWebsite $trackingWebsite): array
    {
        return [
            'entity' => $trackingWebsite
        ];
    }

    protected function update(TrackingWebsite $trackingWebsite): array|RedirectResponse
    {
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $trackingWebsite,
            $this->createForm(TrackingWebsiteType::class),
            $this->getTranslator()->trans('oro.tracking.trackingwebsite.saved_message')
        );
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->container->get(TranslatorInterface::class);
    }

    #[\Override]
    public static function getSubscribedServices(): array
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
