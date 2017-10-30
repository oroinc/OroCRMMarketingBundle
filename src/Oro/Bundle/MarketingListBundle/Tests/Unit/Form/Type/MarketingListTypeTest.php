<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListType;

class MarketingListTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new MarketingListType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('add')
            ->with(
                'name',
                'text',
                ['required' => true]
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(1))
            ->method('add')
            ->with(
                'entity',
                'oro_marketing_list_contact_information_entity_choice',
                ['required' => true]
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(2))
            ->method('add')
            ->with(
                'description',
                'oro_resizeable_rich_text',
                ['required' => false]
            )
            ->will($this->returnSelf());

        $builder->expects($this->at(4))
            ->method('add')
            ->with(
                'definition',
                'hidden',
                ['required' => false]
            )
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'column_column_field_choice_options' => [
                        'exclude_fields' => ['relation_type'],
                    ],
                    'column_column_choice_type'   => 'hidden',
                    'filter_column_choice_type'   => 'oro_entity_field_select',
                    'data_class'                  => 'Oro\Bundle\MarketingListBundle\Entity\MarketingList',
                    'intention'                   => 'marketing_list',
                    'query_type'                  => 'segment',
                ]
            );

        $this->type->configureOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_marketing_list', $this->type->getName());
    }
}
