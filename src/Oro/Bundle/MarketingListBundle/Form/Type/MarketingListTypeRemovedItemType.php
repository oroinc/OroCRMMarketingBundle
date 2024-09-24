<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingListTypeRemovedItemType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('entityId', IntegerType::class, ['required' => true])
            ->add(
                'marketingList',
                EntityType::class,
                [
                    'class'    => 'Oro\Bundle\MarketingListBundle\Entity\MarketingList',
                    'required' => true
                ]
            );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'Oro\Bundle\MarketingListBundle\Entity\MarketingListRemovedItem',
                'csrf_token_id'      => 'marketing_list_removed_item',
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
        return 'oro_marketing_list_removed_item';
    }
}
