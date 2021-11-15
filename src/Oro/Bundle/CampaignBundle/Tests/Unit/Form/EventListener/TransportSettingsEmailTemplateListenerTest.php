<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Form\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Form\EventListener\TransportSettingsEmailTemplateListener;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class TransportSettingsEmailTemplateListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var TokenAccessorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenAccessor;

    /** @var Form|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var TransportSettingsEmailTemplateListener */
    private $listener;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->form = $this->createMock(Form::class);

        $config = $this->createMock(FormConfigInterface::class);
        $config->expects($this->any())
            ->method('getOptions')
            ->willReturn([]);

        $type = $this->createMock(ResolvedFormTypeInterface::class);
        $config->expects($this->any())
            ->method('getType')
            ->willReturn($type);
        $type->expects($this->any())
            ->method('getInnerType')
            ->willReturn(new SubmitType());

        $this->form->expects($this->any())
            ->method('getConfig')
            ->willReturn($config);
        $this->form->expects($this->any())
            ->method('getParent')
            ->willReturnSelf();
        $this->form->expects($this->any())
            ->method('get')
            ->willReturnSelf();

        $this->listener = new TransportSettingsEmailTemplateListener($this->registry, $this->tokenAccessor);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $this->listener->getSubscribedEvents());
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $this->listener->getSubscribedEvents());
    }

    public function testPreSet()
    {
        $marketingList = new MarketingList();
        $marketingList->setEntity(\stdClass::class);

        $emailCampaign = new EmailCampaign();
        $emailCampaign->setMarketingList($marketingList);

        $this->form->expects($this->once())
            ->method('getData')
            ->willReturn($emailCampaign);

        $this->form->expects($this->atLeastOnce())
            ->method('add');

        $event = new FormEvent($this->form, []);

        $this->listener->preSet($event);
    }

    /**
     * @dataProvider preSubmitDataProvider
     */
    public function testPreSubmit(array $data, ?MarketingList $marketingList, bool $expected)
    {
        $repository = $this->createMock(ObjectRepository::class);
        $this->registry->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);
        $repository->expects($this->any())
            ->method('find')
            ->willReturn($marketingList);

        if ($expected) {
            $this->form->expects($this->atLeastOnce())
                ->method('add');
        }

        $this->listener->preSubmit(new FormEvent($this->form, $data));
    }

    public function preSubmitDataProvider(): array
    {
        $marketingList = new MarketingList();
        $marketingList->setEntity(\stdClass::class);

        return [
            [[], null, false],
            [['parentData' => ['marketingList' => 1]], null, false],
            [['parentData' => ['marketingList' => 1]], $marketingList, true],
        ];
    }
}
