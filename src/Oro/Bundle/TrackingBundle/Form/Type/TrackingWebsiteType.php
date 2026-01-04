<?php

namespace Oro\Bundle\TrackingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackingWebsiteType extends AbstractType
{
    public const NAME = 'oro_tracking_website';

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param string $dataClass
     */
    public function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'oro.tracking.trackingwebsite.name.label'
                ]
            )
            ->add(
                'identifier',
                TextType::class,
                [
                    'label'   => 'oro.tracking.trackingwebsite.identifier.label',
                    'tooltip' => 'oro.tracking.form.tooltip.identifier',
                ]
            )
            ->add(
                'url',
                TextType::class,
                [
                    'label' => 'oro.tracking.trackingwebsite.url.label'
                ]
            );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => $this->dataClass,
                'csrf_token_id' => 'tracking_website',
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
