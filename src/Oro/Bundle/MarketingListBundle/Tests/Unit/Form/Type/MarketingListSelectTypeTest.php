<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingListSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListSelectType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new MarketingListSelectType();
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'autocomplete_alias' => 'marketing_lists',
                    'create_form_route'  => 'oro_marketing_list_create',
                    'configs'            => [
                        'placeholder' => 'oro.marketinglist.form.choose_marketing_list'
                    ],
                ]
            );

        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }
}
