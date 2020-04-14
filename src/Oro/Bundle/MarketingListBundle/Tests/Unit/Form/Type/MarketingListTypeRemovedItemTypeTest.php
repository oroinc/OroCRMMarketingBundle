<?php
namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListTypeRemovedItemType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class MarketingListTypeRemovedItemTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MarketingListTypeRemovedItemType
     */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp(): void
    {
        $this->type = new MarketingListTypeRemovedItemType();
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(2))
            ->method('add')
            ->will($this->returnSelf());

        $builder->expects($this->at(0))
            ->method('add')
            ->with('entityId', IntegerType::class, ['required' => true]);

        $builder->expects($this->at(1))
            ->method('add')
            ->with(
                'marketingList',
                EntityType::class,
                [
                    'class'    => 'Oro\Bundle\MarketingListBundle\Entity\MarketingList',
                    'required' => true
                ]
            );

        $this->type->buildForm($builder, array());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }
}
