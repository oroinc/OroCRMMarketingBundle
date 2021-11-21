<?php

namespace Oro\Bundle\CampaignBundle\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\CampaignBundle\Entity\CampaignCodeHistory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that a campaign code is not used yet.
 */
class CampaignCodeValidator extends ConstraintValidator
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CampaignCode) {
            throw new UnexpectedTypeException($constraint, CampaignCode::class);
        }

        if (!$value instanceof Campaign) {
            return;
        }

        $codeHistory = $this->doctrine
            ->getRepository(CampaignCodeHistory::class)
            ->findOneBy(['code' => $value->getCode()]);
        if ($codeHistory && $codeHistory->getCampaign()->getId() != $value->getId()) {
            $this->context->buildViolation($constraint->message)
                ->atPath('code')
                ->addViolation();
        }
    }
}
