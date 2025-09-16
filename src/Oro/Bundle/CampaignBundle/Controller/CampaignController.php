<?php

namespace Oro\Bundle\CampaignBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for marketing campaigns.
 */
#[Route(path: '/campaign')]
class CampaignController extends AbstractController
{
    #[Route(path: '/', name: 'oro_campaign_index')]
    #[Template('@OroCampaign/Campaign/index.html.twig')]
    #[AclAncestor('oro_campaign_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => Campaign::class
        ];
    }

    /**
     * Create campaign
     */
    #[Route(path: '/create', name: 'oro_campaign_create')]
    #[Template('@OroCampaign/Campaign/update.html.twig')]
    #[Acl(id: 'oro_campaign_create', type: 'entity', class: Campaign::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new Campaign());
    }

    /**
     * Edit campaign
     */
    #[Route(path: '/update/{id}', name: 'oro_campaign_update', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    #[Template('@OroCampaign/Campaign/update.html.twig')]
    #[Acl(id: 'oro_campaign_update', type: 'entity', class: Campaign::class, permission: 'EDIT')]
    public function updateAction(Campaign $entity): array|RedirectResponse
    {
        return $this->update($entity);
    }

    /**
     * View campaign
     */
    #[Route(path: '/view/{id}', name: 'oro_campaign_view')]
    #[Template('@OroCampaign/Campaign/view.html.twig')]
    #[Acl(id: 'oro_campaign_view', type: 'entity', class: Campaign::class, permission: 'VIEW')]
    public function viewAction(Campaign $entity): array
    {
        $codesHistory = $this->container->get('doctrine')
            ->getRepository(Campaign::class)
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
        return $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $this->container->get('oro_campaign.campaign.form'),
            $this->container->get(TranslatorInterface::class)->trans('oro.campaign.controller.campaign.saved.message')
        );
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                'oro_campaign.campaign.form' => Form::class,
                UpdateHandlerFacade::class,
                'doctrine' => ManagerRegistry::class
            ]
        );
    }
}
