<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\CampaignType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CampaignTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CampaignType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CampaignType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(0))
            ->method('add')
            ->with('name', TextType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(1))
            ->method('add')
            ->with('code', TextType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('startDate', OroDateType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with('endDate', OroDateType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(4))
            ->method('add')
            ->with('description', OroResizeableRichTextType::class)
            ->will($this->returnSelf());
        $builder->expects($this->at(5))
            ->method('add')
            ->with('budget', OroMoneyType::class)
            ->will($this->returnSelf());

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
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => 'Oro\Bundle\CampaignBundle\Entity\Campaign',
                'validation_groups' => ['Campaign', 'Default']
            ]);

        $this->type->configureOptions($resolver);
    }
}
