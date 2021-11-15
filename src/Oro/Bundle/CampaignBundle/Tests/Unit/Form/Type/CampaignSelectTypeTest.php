<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\CampaignSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CampaignSelectType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new CampaignSelectType();
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(OroEntitySelectOrCreateInlineType::class, $this->type->getParent());
    }
}
