<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCode;
use Symfony\Component\Validator\Constraint;

class CampaignCodeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CampaignCode */
    private $constraint;

    protected function setUp(): void
    {
        $this->constraint = new CampaignCode();
    }

    public function testGetTargets()
    {
        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }

    public function testValidatedBy()
    {
        $this->assertEquals('oro_campaign.campaign_code_validator', $this->constraint->validatedBy());
    }
}
