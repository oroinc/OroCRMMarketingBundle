<?php

namespace Oro\Bundle\CampaignBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This constraint is used to check that a campaign code is not used yet.
 */
class CampaignCode extends Constraint
{
    public string $message = 'This value is already used.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
