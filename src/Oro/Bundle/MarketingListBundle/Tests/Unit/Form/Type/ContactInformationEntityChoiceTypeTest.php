<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MarketingListBundle\Form\Type\ContactInformationEntityChoiceType;

class ContactInformationEntityChoiceTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $provider;

    /**
     * @var ContactInformationEntityChoiceType
     */
    protected $type;

    protected function setUp(): void
    {
        $this->provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new ContactInformationEntityChoiceType($this->provider);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_marketing_list_contact_information_entity_choice', $this->type->getName());
    }
}
