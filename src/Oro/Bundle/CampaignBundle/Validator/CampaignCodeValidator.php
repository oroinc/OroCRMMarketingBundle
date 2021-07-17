<?php

namespace Oro\Bundle\CampaignBundle\Validator;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

        $codeHistory = $this->registry
            ->getRepository('OroCampaignBundle:CampaignCodeHistory')
            ->findOneBy(['code' => $value->getCode()]);
        if ($codeHistory && $codeHistory->getCampaign()->getId() != $value->getId()) {
            /** @var ExecutionContextInterface $context */
            $context = $this->context;
            $context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();
        }
    }
}
