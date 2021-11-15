<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\EventListener\TransportSettingsListener;
use Oro\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TransportSettingsListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var EmailTransportProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $emailTransportProvider;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var TransportSettingsListener */
    private $listener;

    protected function setUp(): void
    {
        $this->emailTransportProvider = $this->createMock(EmailTransportProvider::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->listener = new TransportSettingsListener($this->emailTransportProvider, $this->doctrineHelper);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        ];
        $this->assertEquals($expected, $this->listener->getSubscribedEvents());
    }

    public function testPreSetNoData()
    {
        $event = $this->createMock(FormEvent::class);
        $event->expects($this->never())
            ->method('getForm');
        $this->emailTransportProvider->expects($this->never())
            ->method($this->anything());
        $this->listener->preSet($event);
    }

    public function testPreSetHasTransportHasForm()
    {
        $transportName = 'internal';
        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->once())
            ->method('getTransport')
            ->willReturn($transportName);
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getName')
            ->willReturn($transportName);
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->willReturn('test_type');
        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->willReturn($transport);

        $this->listener->preSet($event);
    }

    public function testPreSetHasTransportNoFormHadBefore()
    {
        $transportName = 'internal';
        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->once())
            ->method('getTransport')
            ->willReturn($transportName);
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getName')
            ->willReturn($transportName);
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->willReturn(null);
        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('has')
            ->with('transportSettings')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('remove')
            ->with('transportSettings');

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->willReturn($transport);

        $this->listener->preSet($event);
    }

    public function testPreSetHasTransportNoForm()
    {
        $transportName = 'internal';
        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->once())
            ->method('getTransport')
            ->willReturn($transportName);
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getName')
            ->willReturn($transportName);
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->willReturn(null);
        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('has')
            ->with('transportSettings')
            ->willReturn(false);
        $form->expects($this->never())
            ->method('remove');

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->willReturn($transport);

        $this->listener->preSet($event);
    }

    public function testPreSetNoTransportSetHasForm()
    {
        $transportName = 'internal';
        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->once())
            ->method('getTransport')
            ->willReturn(null);
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getName')
            ->willReturn($transportName);
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->willReturn('test_type');
        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransports')
            ->willReturn([$transport]);

        $this->listener->preSet($event);
    }

    public function testPostSetNoData()
    {
        $event = $this->createMock(FormEvent::class);
        $event->expects($this->never())
            ->method('getForm');
        $this->emailTransportProvider->expects($this->never())
            ->method($this->anything());
        $this->listener->postSet($event);
    }

    public function testPostSet()
    {
        $transportName = 'internal';
        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->once())
            ->method('getTransport')
            ->willReturn($transportName);

        $transportSubform = $this->createMock(Form::class);
        $transportSubform->expects($this->once())
            ->method('setData')
            ->with($transportName);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('get')
            ->with('transport')
            ->willReturn($transportSubform);

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);
        $this->emailTransportProvider->expects($this->never())
            ->method($this->anything());
        $this->listener->postSet($event);
    }

    public function testPreSubmitTransport()
    {
        $transportName = 'internal';

        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->any())
            ->method('getTransport')
            ->willReturn('other');
        $entity->expects($this->once())
            ->method('setTransport')
            ->willReturn($transportName);

        $data = ['transport' => $transportName];

        $transportSubform = $this->createMock(Form::class);
        $transportSubform->expects($this->once())
            ->method('setData')
            ->with($transportName);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $form->expects($this->any())
            ->method('has')
            ->with('transportSettings')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);
        $form->expects($this->once())
            ->method('get')
            ->with('transport')
            ->willReturn($transportSubform);

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $expectedSetData = $data;
        $expectedSetData['transportSettings']['parentData'] = $data;
        $event->expects($this->once())
            ->method('setData')
            ->with($expectedSetData);

        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getName')
            ->willReturn($transportName);
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->willReturn('test_type');
        $transport->expects($this->once())
            ->method('getSettingsEntityFQCN')
            ->willReturn(\stdClass::class);

        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->willReturn($transport);

        $this->doctrineHelper->expects($this->once())
            ->method('createEntityInstance')
            ->with(\stdClass::class)
            ->willReturn(new \stdClass());

        $this->listener->preSubmit($event);
    }

    public function testPreSubmitTransportSameTransport()
    {
        $transportName = 'internal';

        $entity = $this->createMock(EmailCampaign::class);
        $entity->expects($this->any())
            ->method('getTransport')
            ->willReturn($transportName);
        $entity->expects($this->once())
            ->method('setTransport')
            ->willReturn($transportName);

        $data = ['transport' => $transportName];

        $transportSubform = $this->createMock(Form::class);
        $transportSubform->expects($this->once())
            ->method('setData')
            ->with($transportName);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())
            ->method('getData')
            ->willReturn($entity);
        $form->expects($this->any())
            ->method('has')
            ->with('transportSettings')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);
        $form->expects($this->once())
            ->method('get')
            ->with('transport')
            ->willReturn($transportSubform);

        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($data);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($form);

        $expectedSetData = $data;
        $expectedSetData['transportSettings']['parentData'] = $data;
        $event->expects($this->once())
            ->method('setData')
            ->with($expectedSetData);

        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->any())
            ->method('getName')
            ->willReturn($transportName);
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->willReturn('test_type');
        $transport->expects($this->never())
            ->method('getSettingsEntityFQCN');

        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->willReturn($transport);

        $this->doctrineHelper->expects($this->never())
            ->method('createEntityInstance');

        $this->listener->preSubmit($event);
    }
}
