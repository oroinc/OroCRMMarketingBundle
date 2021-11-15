<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Form\Type;

use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Form\Type\TrackingWebsiteType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackingWebsiteTypeTest extends FormIntegrationTestCase
{
    /** @var TrackingWebsiteType */
    private $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new TrackingWebsiteType(TrackingWebsite::class);
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);

        $builder->expects($this->exactly(3))
            ->method('add')
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
