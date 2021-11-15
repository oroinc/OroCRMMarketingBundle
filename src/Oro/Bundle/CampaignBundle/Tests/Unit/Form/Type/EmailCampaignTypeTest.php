<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\Type\EmailCampaignType;
use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailCampaignTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailCampaignType */
    private $type;

    protected function setUp(): void
    {
        $transportProvider = $this->createMock(EmailTransportProvider::class);

        $this->type = new EmailCampaignType($transportProvider);
    }

    public function testAddEntityFields()
    {
        $builder = $this->createMock(FormBuilder::class);

        $builder->expects($this->atLeastOnce())
            ->method('add')
            ->with($this->isType('string'), $this->isType('string'))
            ->willReturnSelf();

        $builder->expects($this->once())
            ->method('addEventListener')
            ->with(FormEvents::PRE_SET_DATA);

        $subscriber = $this->createMock(EventSubscriberInterface::class);
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($subscriber);

        $this->type->addSubscriber($subscriber);
        $this->type->buildForm($builder, []);
    }

    public function testName()
    {
        $typeName = $this->type->getName();
        $this->assertIsString($typeName);
        $this->assertSame('oro_email_campaign', $typeName);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['data_class' => EmailCampaign::class]);

        $this->type->configureOptions($resolver);
    }
}
