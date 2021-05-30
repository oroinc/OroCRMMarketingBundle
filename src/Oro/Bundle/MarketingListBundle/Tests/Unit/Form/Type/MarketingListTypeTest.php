<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityBundle\Form\Type\EntityFieldSelectType;
use Oro\Bundle\FormBundle\Form\Type\CheckboxType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Form\Type\ContactInformationEntityChoiceType;
use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingListTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new MarketingListType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(5))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class, ['required' => true]],
                ['union', CheckboxType::class],
                ['entity', ContactInformationEntityChoiceType::class, ['required' => true]],
                ['description', OroResizeableRichTextType::class, ['required' => false]],
                ['definition', HiddenType::class, ['required' => false]]
            )
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'column_column_field_choice_options' => [
                        'exclude_fields' => ['relationType'],
                    ],
                    'column_column_choice_type'   => HiddenType::class,
                    'filter_column_choice_type'   => EntityFieldSelectType::class,
                    'data_class'                  => MarketingList::class,
                    'csrf_token_id'               => 'marketing_list',
                    'query_type'                  => 'segment',
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
