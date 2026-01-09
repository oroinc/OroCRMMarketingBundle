<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType;

/**
 * Defines a form type for selecting contact information entities in marketing lists.
 */
class ContactInformationEntityChoiceType extends EntityChoiceType
{
    #[\Override]
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_marketing_list_contact_information_entity_choice';
    }
}
