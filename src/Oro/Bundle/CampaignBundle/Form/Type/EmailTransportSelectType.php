<?php

namespace Oro\Bundle\CampaignBundle\Form\Type;

use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTransportSelectType extends AbstractType
{
    /**
     * @var EmailTransportProvider
     */
    protected $emailTransportProvider;

    public function __construct(EmailTransportProvider $emailTransportProvider)
    {
        $this->emailTransportProvider = $emailTransportProvider;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => $this->emailTransportProvider->getVisibleTransportChoices()
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_campaign_email_transport_select';
    }
}
