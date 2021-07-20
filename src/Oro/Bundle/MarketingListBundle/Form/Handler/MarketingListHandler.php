<?php

namespace Oro\Bundle\MarketingListBundle\Form\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Entity\SegmentType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The handler for the marketing list form.
 */
class MarketingListHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    /**
     * @var array
     */
    protected $marketingListTypeToSegmentTypeMap = [
        MarketingListType::TYPE_DYNAMIC => SegmentType::TYPE_DYNAMIC,
        MarketingListType::TYPE_STATIC => SegmentType::TYPE_STATIC
    ];

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        ManagerRegistry $registry,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->registry = $registry;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request)
    {
        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);
            if ($this->isValid($data, $form, $request)) {
                $this->onSuccess($data);
                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     */
    protected function onSuccess(MarketingList $entity)
    {
        $manager = $this->registry->getManagerForClass(MarketingList::class);
        $manager->persist($entity);
        $manager->flush();
    }

    protected function processSegment(MarketingList $marketingList, FormInterface $form, Request $request)
    {
        $requestData = $request->get($form->getName());
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

        $manager = $this->registry->getManagerForClass(SegmentType::class);

        return $manager->find('OroSegmentBundle:SegmentType', $segmentTypeName);
    }

    /**
     * Validate Marketing List.
     *
     * @param MarketingList $marketingList
     * @param FormInterface $form
     * @param Request $request
     * @return bool
     */
    protected function isValid(MarketingList $marketingList, FormInterface $form, Request $request)
    {
        $isValid = $form->isValid();
        if ($isValid && !$marketingList->isManual()) {
            $this->processSegment($marketingList, $form, $request);
            $errors = $this->validator->validate(
                $marketingList->getSegment(),
                null,
                [Constraint::DEFAULT_GROUP, 'marketing_list']
            );
            if (count($errors) > 0) {
                /** @var ConstraintViolationInterface $error */
                foreach ($errors as $error) {
                    $form->addError(
                        new FormError(
                            $error->getMessage(),
                            $error->getMessageTemplate(),
                            $error->getParameters(),
                            $error->getPlural()
                        )
                    );
                }
            }
            $isValid = $form->isValid();
        }

        return $isValid;
    }
}
