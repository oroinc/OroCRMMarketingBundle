<?php

namespace Oro\Bundle\MarketingListBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\EntityBundle\Form\Type\EntityFieldSelectType;
use Oro\Bundle\FormBundle\Form\Type\CheckboxType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType as MarketingListTypeEntity;
use Oro\Bundle\QueryDesignerBundle\Form\Type\AbstractQueryDesignerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Marketing list form type
 * Used for creating marketing lists, extends abstract query designer
 */
class MarketingListType extends AbstractQueryDesignerType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('union', CheckboxType::class)
            ->add('entity', ContactInformationEntityChoiceType::class, ['required' => true])
            ->add('description', OroResizeableRichTextType::class, ['required' => false]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var MarketingList $marketingList */
                $marketingList = $event->getData();
                $form = $event->getForm();
                if ($marketingList && $marketingList->getId() && $marketingList->isManual()) {
                    $qb = function (EntityRepository $er) {
                        return $er->createQueryBuilder('mlt')
                            ->andWhere('mlt.name = :manualTypeName')
                            ->setParameter('manualTypeName', MarketingListTypeEntity::TYPE_MANUAL);
                    };
                } else {
                    $qb = function (EntityRepository $er) {
                        return $er->createQueryBuilder('mlt')
                            ->andWhere('mlt.name != :manualTypeName')
                            ->setParameter('manualTypeName', MarketingListTypeEntity::TYPE_MANUAL)
                            ->addOrderBy('mlt.name', 'ASC');
                    };
                }

                $form->add(
                    'type',
                    EntityType::class,
                    [
                        'class' => 'OroMarketingListBundle:MarketingListType',
                        'choice_label' => 'label',
                        'required' => true,
                        'placeholder' => 'oro.marketinglist.form.choose_marketing_list_type',
                        'query_builder' => $qb
                    ]
                );
            }
        );

        parent::buildForm($builder, $options);
    }

    /**
     * Gets the default options for this type.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'column_column_field_choice_options' => [
                'exclude_fields' => ['relationType'],
            ],
            'column_column_choice_type' => HiddenType::class,
            'filter_column_choice_type' => EntityFieldSelectType::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $options = array_merge(
            $this->getDefaultOptions(),
            [
                'data_class' => MarketingList::class,
                'csrf_token_id' => 'marketing_list',
                'query_type' => 'segment',
            ]
        );

        $resolver->setDefaults($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_marketing_list';
    }
}
