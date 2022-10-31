<?php

namespace Oro\Bundle\CampaignBundle\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * The listener for setting email template choices based on EmailCampaign{MarketingList} entity class.
 */
class TransportSettingsEmailTemplateListener implements EventSubscriberInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var TokenAccessorInterface
     */
    protected $tokenAccessor;

    public function __construct(ManagerRegistry $registry, TokenAccessorInterface $tokenAccessor)
    {
        $this->registry = $registry;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        ];
    }

    /**
     * Fill template choices based on existing EmailCampaign{MarketingList} entity class.
     */
    public function preSet(FormEvent $event)
    {
        $entityName = $event->getForm()->getParent()->getData()->getEntityName();
        $this->fillEmailTemplateChoices($event->getForm(), $entityName);
    }

    /**
     * Fill template choices based on new EmailCampaign{MarketingList} entity class
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data['parentData']['marketingList'])) {
            return;
        }

        $marketingList = $this->getMarketingListById((int)$data['parentData']['marketingList']);
        if (is_null($marketingList)) {
            return;
        }

        $entityName = $marketingList->getEntity();
        $this->fillEmailTemplateChoices($event->getForm(), $entityName);
    }

    /**
     * @param int $id
     *
     * @return MarketingList
     */
    protected function getMarketingListById($id)
    {
        return $this->registry
            ->getRepository('OroMarketingListBundle:MarketingList')
            ->find($id);
    }

    protected function fillEmailTemplateChoices(FormInterface $form, ?string $entityName): void
    {
        $includeNonEntity = $form->get('template')?->getConfig()->getOption('includeNonEntity') ?? false;
        $includeSystemTpl = $form->get('template')?->getConfig()->getOption('includeSystemTemplates') ?? true;

        FormUtils::replaceField(
            $form,
            'template',
            [
                'selectedEntity' => $entityName,
                'query_builder'  => function (EmailTemplateRepository $templateRepository) use (
                    $entityName,
                    $includeNonEntity,
                    $includeSystemTpl
                ) {
                    return $templateRepository->getEntityTemplatesQueryBuilder(
                        $entityName,
                        $this->tokenAccessor->getOrganization(),
                        $includeNonEntity,
                        $includeSystemTpl
                    );
                }
            ],
            ['choices']
        );
    }
}
