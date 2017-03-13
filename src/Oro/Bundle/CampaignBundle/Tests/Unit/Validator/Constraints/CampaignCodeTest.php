<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

use Oro\Bundle\CampaignBundle\Validator\Constraints\CampaignCode;

class CampaignCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $options;

    /**
     * @var CampaignCode
     */
    protected $constraint;

    protected function setUp()
    {
        $this->constraint = new CampaignCode($this->options);
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
