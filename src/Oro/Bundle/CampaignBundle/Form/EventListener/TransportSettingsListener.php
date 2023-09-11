<?php

namespace Oro\Bundle\CampaignBundle\Form\EventListener;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class TransportSettingsListener implements EventSubscriberInterface
{
    /**
     * @var EmailTransportProvider
     */
    protected $emailTransportProvider;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    public function __construct(EmailTransportProvider $emailTransportProvider, DoctrineHelper $doctrineHelper)
    {
        $this->emailTransportProvider = $emailTransportProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        ];
    }

    /**
     * Add Transport Settings form if any for existing entities.
     */
    public function preSet(FormEvent $event)
    {
        /** @var EmailCampaign $data */
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        $selectedTransport = $this->getSelectedTransport($data->getTransport());
        if ($selectedTransport) {
            $this->addTransportSettingsForm($selectedTransport, $event->getForm());
            $data->setTransport($selectedTransport->getName());
        }
        $event->setData($data);
    }

    /**
     * Set correct transport setting value.
     */
    public function postSet(FormEvent $event)
    {
        /** @var EmailCampaign $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $form = $event->getForm();
        $form->get('transport')->setData($data->getTransport());
    }

    /**
     * Change transport settings subform to form matching transport passed in request.
     * Pass top level data to transportSettings.
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        $formData = $form->getData();

        $transportName = isset($data['transport']) ? $data['transport'] : '';

        $selectedTransport = $this->getSelectedTransport($transportName);
        if ($selectedTransport->getName() != $formData->getTransport()) {
            $newSettings = $this->doctrineHelper
                ->createEntityInstance($selectedTransport->getSettingsEntityFQCN());
            $formData->setTransportSettings($newSettings);
        }

        if ($selectedTransport) {
            $this->addTransportSettingsForm($selectedTransport, $form);
            $formData->setTransport($selectedTransport->getName());
            $form->get('transport')->setData($selectedTransport->getName());
        }

        if ($form->has('transportSettings')) {
            $parentData = $data;
            unset($parentData['transportSettings']);
            $data['transportSettings']['parentData'] = $parentData;
        }

        $event->setData($data);
    }

    protected function addTransportSettingsForm(TransportInterface $selectedTransport, FormInterface $form)
    {
        if ($selectedTransport) {
            $transportSettingsFormType = $selectedTransport->getSettingsFormType();

            if ($transportSettingsFormType) {
                $form->add('transportSettings', $transportSettingsFormType, ['required' => true]);
            } elseif ($form->has('transportSettings')) {
                $form->remove('transportSettings');
            }
        }
    }

    /**
     * @param string $selectedTransportName
     * @return TransportInterface
     */
    protected function getSelectedTransport($selectedTransportName)
    {
        if ($selectedTransportName) {
            $selectedTransport = $this->emailTransportProvider->getTransportByName($selectedTransportName);
        } else {
            $transportChoices = $this->emailTransportProvider->getTransports();
            $selectedTransport = reset($transportChoices);
        }

        return $selectedTransport;
    }
}
