<?php

namespace Oro\Bundle\CampaignBundle\Validator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Translation\TranslatorInterface;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCode;

class CampaignCodeValidator extends ConstraintValidator
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ManagerRegistry $registry
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ManagerRegistry $registry,
        TranslatorInterface $translator
    ) {
        $this->registry   = $registry;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Campaign) {
            return;
        }

        $code = $this->registry
            ->getRepository('OroCampaignBundle:CampaignCode')
            ->findOneBy(['code' => $value->getCode()]);
        if ($code && $code->getCampaign()->getId() != $value->getId()) {
            /** @var ExecutionContextInterface $context */
            $context = $this->context;
            $context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();
        }
    }
}
