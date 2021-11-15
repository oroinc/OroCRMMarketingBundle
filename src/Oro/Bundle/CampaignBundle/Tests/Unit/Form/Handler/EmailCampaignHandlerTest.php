<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\Handler\EmailCampaignHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class EmailCampaignHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const FORM_DATA = ['field' => 'value'];

    /** @var Request */
    private $request;

    /** @var Form|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var EmailCampaignHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->form = $this->createMock(Form::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->handler = new EmailCampaignHandler($requestStack, $this->form, $this->registry);
    }

    public function testProcessGet()
    {
        $data = $this->createMock(EmailCampaign::class);

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
        $data = $this->createMock(EmailCampaign::class);

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
            ->willReturn(false);

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcessUpdateMarker()
    {
        $data = $this->createMock(EmailCampaign::class);

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
        $data = $this->createMock(EmailCampaign::class);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('persist')
            ->with($data);
        $manager->expects($this->once())
            ->method('flush');
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('OroCampaignBundle:EmailCampaign')
            ->willReturn($manager);

        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->assertTrue($this->handler->process($data));
    }
}
