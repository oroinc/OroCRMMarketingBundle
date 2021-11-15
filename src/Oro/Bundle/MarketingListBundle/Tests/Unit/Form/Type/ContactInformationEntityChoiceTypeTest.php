<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\MarketingListBundle\Form\Type\ContactInformationEntityChoiceType;

class ContactInformationEntityChoiceTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var EntityProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    /** @var ContactInformationEntityChoiceType */
    private $type;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(EntityProvider::class);

        $this->type = new ContactInformationEntityChoiceType($this->provider);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_marketing_list_contact_information_entity_choice', $this->type->getName());
    }
}
