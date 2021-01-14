<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CampaignBundle\Form\Handler\EmailCampaignHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailCampaignHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $form;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    /**
     * @var EmailCampaignHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Doctrine\Persistence\ManagerRegistry')
            ->getMockForAbstractClass();

        $this->handler = new EmailCampaignHandler($requestStack, $this->form, $this->registry);
    }

    public function testProcessGet()
    {
        $data = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->setMethod('GET');
        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcessUpdateInvalid()
    {
        $data = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcessUpdateMarker()
    {
        $data = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->form->expects($this->any())
            ->method('getName')
            ->willReturn('formName');

        $this->request->initialize([], [
            EmailCampaignHandler::UPDATE_MARKER => true,
            'formName' => self::FORM_DATA
        ]);
        $this->request->setMethod('PUT');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->request->request->set(EmailCampaignHandler::UPDATE_MARKER, true);
        $this->form->expects($this->never())
            ->method('isValid');

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcess()
    {
        $data = $this->getMockBuilder('Oro\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $manager = $this->getMockBuilder('\Doctrine\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())
            ->method('persist')
            ->with($data);
        $manager->expects($this->once())
            ->method('flush');
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('OroCampaignBundle:EmailCampaign')
            ->will($this->returnValue($manager));

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->assertTrue($this->handler->process($data));
    }
}
