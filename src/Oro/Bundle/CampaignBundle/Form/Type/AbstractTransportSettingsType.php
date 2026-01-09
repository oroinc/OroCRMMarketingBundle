<?php

namespace Oro\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Provides common functionality for email campaign transport settings form types.
 *
 * This base class manages event subscribers and handles common form events for transport settings.
 * It provides infrastructure for registering custom event subscribers and cleaning up parent data
 * during form submission. Subclasses should extend this to create transport-specific settings forms.
 */
abstract class AbstractTransportSettingsType extends AbstractType
{
    /**
     * @var EventSubscriberInterface[]
     */
    protected $subscribers = [];

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->subscribers as $subscriber) {
            $builder->addEventSubscriber($subscriber);
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                unset($data['parentData']);
                $event->setData($data);
            }
        );
    }
}
