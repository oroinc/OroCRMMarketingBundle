<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting MarketingList entity
 */
class MarketingListSelectType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'marketing_lists',
                'create_form_route' => 'oro_marketing_list_create',
                'configs' => [
                    'placeholder' => 'oro.marketinglist.form.choose_marketing_list'
                ],
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_marketing_list_select';
    }
}
