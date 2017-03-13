<?php

namespace Oro\Bundle\CampaignBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class CampaignCode extends Constraint
{
    /**
     * @var string
     */
    public $message = 'This value is already used.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_campaign.campaign_code_validator';
    }
}
