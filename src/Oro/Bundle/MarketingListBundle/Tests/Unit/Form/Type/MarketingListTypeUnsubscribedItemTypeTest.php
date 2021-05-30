<?php
namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListTypeUnsubscribedItemType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingListTypeUnsubscribedItemTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var MarketingListTypeUnsubscribedItemType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new MarketingListTypeUnsubscribedItemType();
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                ['entityId', IntegerType::class, ['required' => true]],
                [
                    'marketingList',
                    EntityType::class,
                    [
                        'class'    => MarketingList::class,
                        'required' => true
                    ]
                ]
            )
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }
}
