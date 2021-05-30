<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Form\Type\CampaignType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CampaignType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new CampaignType();
    }

    public function testAddEntityFields()
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(7))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class],
                ['code', TextType::class],
                ['startDate', OroDateType::class],
                ['endDate', OroDateType::class],
                ['description', OroResizeableRichTextType::class],
                ['budget', OroMoneyType::class],
                ['reportPeriod', ChoiceType::class]
            )
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }

    public function testName()
    {
        $typeName = $this->type->getName();
        $this->assertIsString($typeName);
        $this->assertSame('oro_campaign_form', $typeName);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => Campaign::class,
                'validation_groups' => ['Campaign', 'Default']
            ]);

        $this->type->configureOptions($resolver);
    }
}
