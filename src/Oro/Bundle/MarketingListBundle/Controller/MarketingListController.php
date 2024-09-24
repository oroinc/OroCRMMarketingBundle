<?php

namespace Oro\Bundle\MarketingListBundle\Controller;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\MarketingListBundle\Datagrid\ConfigurationProvider;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Form\Handler\MarketingListHandler;
use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListType;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\Manager;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for MarketingList entity
 */
#[Route(path: '/marketing-list')]
class MarketingListController extends AbstractController
{
    #[Route(path: '/', name: 'oro_marketing_list_index')]
    #[Template]
    #[AclAncestor('oro_marketing_list_view')]
    public function indexAction(): array
    {
        return [
            'entity_class' => MarketingList::class
        ];
    }

    #[Route(path: '/view/{id}', name: 'oro_marketing_list_view', requirements: ['id' => '\d+'], defaults: ['id' => 0])]
    #[Template]
    #[Acl(id: 'oro_marketing_list_view', type: 'entity', class: MarketingList::class, permission: 'VIEW')]
    public function viewAction(MarketingList $entity): array
    {
        $this->checkMarketingList($entity);

        $entityConfig = $this->getEntityProvider()->getEntity($entity->getEntity());

        return [
            'entity'   => $entity,
            'config'   => $entityConfig,
            'gridName' => ConfigurationProvider::GRID_PREFIX . $entity->getId()
        ];
    }

    #[Route(path: '/create', name: 'oro_marketing_list_create')]
    #[Template('@OroMarketingList/MarketingList/update.html.twig')]
    #[Acl(id: 'oro_marketing_list_create', type: 'entity', class: MarketingList::class, permission: 'CREATE')]
    public function createAction(): array|RedirectResponse
    {
        return $this->update(new MarketingList());
    }

    #[Route(
        path: '/update/{id}',
        name: 'oro_marketing_list_update',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0]
    )]
    #[Template]
    #[Acl(id: 'oro_marketing_list_update', type: 'entity', class: MarketingList::class, permission: 'EDIT')]
    public function updateAction(MarketingList $entity): array|RedirectResponse
    {
        $this->checkMarketingList($entity);

        return $this->update($entity);
    }

    protected function update(MarketingList $entity): array|RedirectResponse
    {
        $form = $this->container->get('form.factory')
            ->createNamed('oro_marketing_list_form', MarketingListType::class);

        $response = $this->container->get(UpdateHandlerFacade::class)->update(
            $entity,
            $form,
            $this->container->get(TranslatorInterface::class)->trans('oro.marketinglist.entity.saved'),
            null,
            $this->container->get(MarketingListHandler::class)
        );

        if (\is_array($response)) {
            return array_merge(
                $response,
                [
                    'entities' => $this->getEntityProvider()->getEntities(),
                    'metadata' => $this->container->get(Manager::class)->getMetadata('segment')
                ]
            );
        }

        return $response;
    }

    protected function checkMarketingList(MarketingList $marketingList): void
    {
        if ($marketingList->getEntity() &&
            !$this->getFeatureChecker()->isResourceEnabled($marketingList->getEntity(), 'entities')
        ) {
            throw $this->createNotFoundException();
        }
    }

    protected function getFeatureChecker(): FeatureChecker
    {
        return $this->container->get(FeatureChecker::class);
    }

    private function getEntityProvider(): EntityProvider
    {
        return $this->container->get('oro_marketing_list.entity_provider');
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                'oro_marketing_list.entity_provider' => EntityProvider::class,
                TranslatorInterface::class,
                FeatureChecker::class,
                UpdateHandlerFacade::class,
                ValidatorInterface::class,
                Manager::class,
                MarketingListHandler::class,
                'form.factory' => FormFactoryInterface::class
            ]
        );
    }
}
