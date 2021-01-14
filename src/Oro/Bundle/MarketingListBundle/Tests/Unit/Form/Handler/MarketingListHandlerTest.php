<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\MarketingListBundle\Form\Handler\MarketingListHandler;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Entity\SegmentType;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MarketingListHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['definition' => 'test'];
    const FORM_NAME = 'test_form';

    /**
     * @var MarketingListHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Form
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

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ValidatorInterface
     */
    protected $validator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var MarketingList
     */
    protected $testEntity;

    protected function setUp(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry $registry */
        $registry = $this->createMock(ManagerRegistry::class);

        $this->manager = $this->createMock(EntityManager::class);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->manager));

        $this->form = $this->createMock(Form::class);

        $this->request = new Request();

        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->testEntity = new MarketingList();
        $this->handler = new MarketingListHandler(
            $registry,
            $this->validator,
            $this->translator
        );
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->testEntity);
    }

    public function testProcess()
    {
        $this->request->initialize([], [self::FORM_NAME => self::FORM_DATA]);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->assertProcessSegment();

        $this->form->expects($this->exactly(2))
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnValue('Marketing List test segment'));

        $this->form->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::FORM_NAME));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->testEntity);
        $this->manager->expects($this->once())
            ->method('flush');
        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(Segment::class), null, ['Default', 'marketing_list'])
            ->will($this->returnValue([]));

        $this->assertTrue($this->handler->process($this->testEntity, $this->form, $this->request));

        $this->assertSegmentData();
    }

    public function testProcessInvalidEntity()
    {
        $this->request->initialize([], [self::FORM_NAME => self::FORM_DATA]);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->manager->expects($this->never())
            ->method('find');
        $this->translator->expects($this->never())
            ->method('trans');

        $this->form->expects($this->any())
            ->method('getName')
            ->willReturn(self::FORM_NAME);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->manager->expects($this->never())
            ->method('persist');
        $this->manager->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->testEntity, $this->form, $this->request));

        $this->assertNull($this->testEntity->getSegment());
    }

    public function testProcessInvalidSegment()
    {
        $this->request->initialize([], [self::FORM_NAME => self::FORM_DATA]);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->any())
            ->method('getName')
            ->willReturn(self::FORM_NAME);

        $this->assertProcessSegment();

        $this->form->expects($this->exactly(2))
            ->method('isValid')
            ->willReturnOnConsecutiveCalls(true, false);

        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('message'));
        $violation->expects($this->once())
            ->method('getMessageTemplate')
            ->will($this->returnValue('message template'));
        $violation->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue(['test']));
        $violation->expects($this->once())
            ->method('getPlural')
            ->will($this->returnValue(1));
        $errors = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf(Segment::class), null, ['Default', 'marketing_list'])
            ->will($this->returnValue($errors));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnValue('Marketing List test segment'));

        $this->form->expects($this->once())
            ->method('addError')
            ->with(
                new FormError(
                    'message',
                    'message template',
                    ['test'],
                    1
                )
            );

        $this->manager->expects($this->never())
            ->method('persist');
        $this->manager->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->testEntity, $this->form, $this->request));

        $this->assertSegmentData();
    }

    protected function assertProcessSegment()
    {
        $businessUnit = $this->createMock(BusinessUnit::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|User $owner */
        $owner = $this->createMock(User::class);
        $owner->expects($this->atLeastOnce())
            ->method('getOwner')
            ->will($this->returnValue($businessUnit));

        $this->testEntity->setName('test')
            ->setDescription('description')
            ->setType(new MarketingListType(MarketingListType::TYPE_DYNAMIC))
            ->setOwner($owner);

        $segmentType = new SegmentType(SegmentType::TYPE_DYNAMIC);
        $this->manager->expects($this->once())
            ->method('find')
            ->with('OroSegmentBundle:SegmentType', MarketingListType::TYPE_DYNAMIC)
            ->will($this->returnValue($segmentType));
    }

    protected function assertSegmentData()
    {
        $segmentType = new SegmentType(SegmentType::TYPE_DYNAMIC);
        $segment = $this->testEntity->getSegment();
        $this->assertEquals('Marketing List test segment', $segment->getName());
        $this->assertEquals('test', $segment->getDefinition());
        $this->assertEquals($this->testEntity->getOwner()->getOwner(), $segment->getOwner());
        $this->assertEquals($segmentType, $segment->getType());
    }

    public function testProcessWrongRequest()
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->testEntity, $this->form, $this->request));
    }
}
