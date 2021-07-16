<?php

namespace Oro\Bundle\CampaignBundle\Form\Type;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label'    => 'oro.campaign.name.label',
                    'required' => true,
                    'tooltip'  => 'oro.campaign.name.description',
                ]
            )
            ->add(
                'code',
                TextType::class,
                [
                    'label'    => 'oro.campaign.code.label',
                    'required' => true,
                    'tooltip'  => 'oro.campaign.code.description',
                ]
            )
            ->add(
                'startDate',
                OroDateType::class,
                [
                    'label'    => 'oro.campaign.start_date.label',
                    'required' => false,
                ]
            )
            ->add(
                'endDate',
                OroDateType::class,
                [
                    'label'    => 'oro.campaign.end_date.label',
                    'required' => false,
                ]
            )->add(
                'description',
                OroResizeableRichTextType::class,
                [
                    'label'    => 'oro.campaign.description.label',
                    'required' => false,
                ]
            )
            ->add(
                'budget',
                OroMoneyType::class,
                [
                    'label'    => 'oro.campaign.budget.label',
                    'required' => false,
                ]
            )
            ->add(
                'reportPeriod',
                ChoiceType::class,
                [
                    'label'   => 'oro.campaign.report_period.label',
                    'choices' => [
                        'oro.campaign.report_period.hour' => Campaign::PERIOD_HOURLY,
                        'oro.campaign.report_period.day' => Campaign::PERIOD_DAILY,
                        'oro.campaign.report_period.month' => Campaign::PERIOD_MONTHLY,
                    ],
                    'tooltip' => 'oro.campaign.report_period.description'
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Oro\Bundle\CampaignBundle\Entity\Campaign',
            'validation_groups' => ['Campaign', 'Default']
        ]);
    }

    /**
     * @return string
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
        return 'oro_campaign_form';
    }
}
