<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Twig;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class ContactInformationFieldsExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var ContactInformationFieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $helper;

    /** @var ContactInformationFieldsExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(ContactInformationFieldHelper::class);

        $container = self::getContainerBuilder()
            ->add('oro_marketing_list.contact_information_field_helper', $this->helper)
            ->getContainer($this);

        $this->extension = new ContactInformationFieldsExtension($container);
    }

    public function testGetContactInformationFieldsInfoNoEntity()
    {
        $this->helper->expects($this->never())
            ->method($this->anything());

        $this->assertEmpty(
            self::callTwigFunction($this->extension, 'get_contact_information_fields_info', [null])
        );
    }

    public function testGetContactInformationFieldsInfo()
    {
        $entity = \stdClass::class;
        $contactInformation = [['name' => 'test']];

        $this->helper->expects($this->once())
            ->method('getEntityContactInformationFieldsInfo')
            ->with($entity)
            ->willReturn($contactInformation);

        $this->assertEquals(
            $contactInformation,
            self::callTwigFunction($this->extension, 'get_contact_information_fields_info', [$entity])
        );
    }
}
