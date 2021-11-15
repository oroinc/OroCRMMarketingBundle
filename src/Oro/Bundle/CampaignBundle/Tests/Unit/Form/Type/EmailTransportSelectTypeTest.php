<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\EmailTransportSelectType;
use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTransportSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailTransportProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $emailTransportProvider;

    /** @var EmailTransportSelectType */
    private $type;

    protected function setUp(): void
    {
        $this->emailTransportProvider = $this->createMock(EmailTransportProvider::class);

        $this->type = new EmailTransportSelectType($this->emailTransportProvider);
    }

    public function testConfigureOptions()
    {
        $choices = ['internal' => 'oro.campaign.emailcampaign.transport.internal'];
        $this->emailTransportProvider->expects($this->once())
            ->method('getVisibleTransportChoices')
            ->willReturn($choices);
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(['choices' => $choices]);
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(ChoiceType::class, $this->type->getParent());
    }
}
