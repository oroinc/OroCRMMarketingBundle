<?php

namespace Oro\Bundle\MarketingListBundle\Form\Handler;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ValidatorInterface;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Entity\SegmentType;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

class MarketingListHandler
{
    /**
     * @var array
     */
    protected $marketingListTypeToSegmentTypeMap = [
        MarketingListType::TYPE_DYNAMIC => SegmentType::TYPE_DYNAMIC,
        MarketingListType::TYPE_STATIC => SegmentType::TYPE_STATIC
    ];

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param RegistryInterface $doctrine
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        RegistryInterface $doctrine,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $doctrine->getManager();
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * Process form
     *
     * @param  MarketingList $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(MarketingList $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($request);
            if (!$entity->isManual()) {
                $this->processSegment($entity);
            }

            if ($this->isValid($entity)) {
                $this->onSuccess($entity);
                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param MarketingList $entity
     */
    protected function onSuccess(MarketingList $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * @param MarketingList $marketingList
     */
    protected function processSegment(MarketingList $marketingList)
    {
        $requestData = $this->requestStack->getCurrentRequest()->get($this->form->getName());
        $segment = $marketingList->getSegment();
        if (!$segment) {
            $segment = new Segment();
        }
        $segmentName = $this->translator->trans(
            'oro.marketinglist.segment',
            ['%name%' => $marketingList->getName()]
        );
        $segment
            ->setName($segmentName)
            ->setEntity($marketingList->getEntity())
            ->setType($this->getSegmentTypeByMarketingListType($marketingList->getType()))
            ->setDefinition($requestData['definition'])
            ->setOrganization($marketingList->getOrganization());

        $owner = $marketingList->getOwner()->getOwner();
        if (null !== $owner) {
            $segment->setOwner($owner);
        }

        $marketingList->setSegment($segment);
    }

    /**
     * @param MarketingListType $marketingListType
     * @return SegmentType
     */
    protected function getSegmentTypeByMarketingListType(MarketingListType $marketingListType)
    {
        $segmentTypeName = $this->marketingListTypeToSegmentTypeMap[$marketingListType->getName()];

        return $this->manager->find('OroSegmentBundle:SegmentType', $segmentTypeName);
    }

    /**
     * Validate Marketing List.
     *
     * @param MarketingList $marketingList
     * @return bool
     */
    protected function isValid(MarketingList $marketingList)
    {
        if (!$marketingList->isManual()) {
            $errors = $this->validator->validate(
                $marketingList->getSegment(),
                [Constraint::DEFAULT_GROUP, 'marketing_list']
            );
            if (count($errors) > 0) {
                /** @var ConstraintViolationInterface $error */
                foreach ($errors as $error) {
                    $this->form->addError(
                        new FormError(
                            $error->getMessage(),
                            $error->getMessageTemplate(),
                            $error->getMessageParameters(),
                            $error->getMessagePluralization()
                        )
                    );
                }
            }
        }

        return $this->form->isValid();
    }
}
