<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Form\Type;

use Oro\Bundle\TrackingBundle\Form\Type\TrackingWebsiteType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class TrackingWebsiteTypeTest extends FormIntegrationTestCase
{
    /**
     * @var TrackingWebsiteType
     */
    protected $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new TrackingWebsiteType(
            'Oro\Bundle\TrackingBundle\Entity\TrackingWebsite'
        );
    }

    public function testBuildForm()
    {
        $builder = $this
            ->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder
            ->expects($this->exactly(3))
            ->method('add')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');

        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }
}
