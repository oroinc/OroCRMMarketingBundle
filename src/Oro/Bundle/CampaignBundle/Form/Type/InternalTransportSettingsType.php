<?php

namespace Oro\Bundle\CampaignBundle\Form\Type;

use Oro\Bundle\EmailBundle\Form\Type\EmailTemplateSelectType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for transport settings
 */
class InternalTransportSettingsType extends AbstractTransportSettingsType
{
    const NAME = 'oro_campaign_internal_transport_settings';

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'template',
                EmailTemplateSelectType::class,
                [
                    'label' => 'oro.campaign.emailcampaign.template.label',
                    'required' => true,
                    'depends_on_parent_field' => 'marketingList',
                    'data_route' => 'oro_api_get_emailcampaign_email_templates',
                    'data_route_parameter' => 'id',
                    'include_non_entity_templates' => true,
                    'include_system_templates' => false
                ]
            );

        parent::buildForm($builder, $options);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\Bundle\CampaignBundle\Entity\InternalTransportSettings'
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
