<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\InternalTransportSettingsType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InternalTransportSettingsTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var InternalTransportSettingsType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new InternalTransportSettingsType();
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));

        $this->type->configureOptions($resolver);
    }

    public function testBuildForm()
    {
        $formBuilder = $this->createMock(FormBuilder::class);
        $subscriber = $this->createMock(EventSubscriberInterface::class);

        $formBuilder->expects($this->once())
            ->method('add')
            ->willReturnSelf();

        $formBuilder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->equalTo($subscriber))
            ->willReturnSelf();

        $formBuilder->expects($this->once())
            ->method('addEventListener')
            ->with($this->equalTo(FormEvents::PRE_SUBMIT), $this->isType('callable'))
            ->willReturnSelf();

        $this->type->addSubscriber($subscriber);
        $this->type->buildForm($formBuilder, []);
    }
}
