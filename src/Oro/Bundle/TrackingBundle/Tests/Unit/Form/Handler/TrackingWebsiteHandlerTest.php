<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Form\Handler\TrackingWebsiteHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TrackingWebsiteHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const FORM_DATA = ['field' => 'value'];

    /** @var Form|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var TrackingWebsiteHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $this->manager = $this->createMock(ObjectManager::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->handler = new TrackingWebsiteHandler(
            $this->form,
            $requestStack,
            $this->manager
        );
    }

    /**
     * @dataProvider processProvider
     */
    public function testProcess(string $method, bool $isFormValid, bool $isFlushCalled): void
    {
        $entity = new TrackingWebsite();

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->any())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->any())
            ->method('isValid')
            ->willReturn($isFormValid);

        if ($isFlushCalled) {
            $this->manager->expects($this->once())
                ->method('persist')
                ->with($this->equalTo($entity))
                ->willReturn(true);
            $this->manager->expects($this->once())
                ->method('flush');
        }

        $this->handler->process($entity);
    }

    public function processProvider(): array
    {
        return [
            ['POST', false, false],
            ['POST', true, true],
            ['GET', false, false],
            ['GET', true, false],
        ];
    }
}
