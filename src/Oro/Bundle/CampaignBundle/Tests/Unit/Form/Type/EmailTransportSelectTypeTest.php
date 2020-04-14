<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CampaignBundle\Form\Type\EmailTransportSelectType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EmailTransportSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EmailTransportSelectType
     */
    protected $type;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $emailTransportProvider;

    /**
     * Setup test env
     */
    protected function setUp(): void
    {
        $this->emailTransportProvider = $this
            ->getMockBuilder('Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new EmailTransportSelectType($this->emailTransportProvider);
    }

    public function testConfigureOptions()
    {
        $choices = ['internal' => 'oro.campaign.emailcampaign.transport.internal'];
        $this->emailTransportProvider
            ->expects($this->once())
            ->method('getVisibleTransportChoices')
            ->will($this->returnValue($choices));
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with([
                'choices' => $choices,
            ]);
        $this->type->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(ChoiceType::class, $this->type->getParent());
    }
}
