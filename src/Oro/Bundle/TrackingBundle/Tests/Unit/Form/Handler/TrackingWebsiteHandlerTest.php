<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\TrackingBundle\Form\Handler\TrackingWebsiteHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TrackingWebsiteHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $manager;

    protected function setUp(): void
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->manager = $this->getMockBuilder('Doctrine\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $method
     * @param bool   $isFormValid
     * @param bool   $isFlushCalled
     *
     * @dataProvider processProvider
     */
    public function testProcess($method, $isFormValid, $isFlushCalled)
    {
        $entity = new TrackingWebsite();

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form
            ->expects($this->any())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form
            ->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($isFormValid));

        if ($isFlushCalled) {
            $this->manager
                ->expects($this->once())
                ->method('persist')
                ->with($this->equalTo($entity))
                ->will($this->returnValue(true));

            $this->manager
                ->expects($this->once())
                ->method('flush');
        }

        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $handler = new TrackingWebsiteHandler(
            $this->form,
            $requestStack,
            $this->manager
        );

        $handler->process($entity);
    }

    /**
     * @return array
     */
    public function processProvider()
    {
        return [
            ['POST', false, false],
            ['POST', true, true],
            ['GET', false, false],
            ['GET', true, false],
        ];
    }
}
