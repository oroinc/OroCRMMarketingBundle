<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Twig;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;

class ContactInformationFieldsExtensionTest extends \PHPUnit\Framework\TestCase
{
    use TwigExtensionTestCaseTrait;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $helper;

    /** @var ContactInformationFieldsExtension */
    protected $extension;

    protected function setUp(): void
    {
        $this->helper = $this->getMockBuilder(ContactInformationFieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $container = self::getContainerBuilder()
            ->add('oro_marketing_list.contact_information_field_helper', $this->helper)
            ->getContainer($this);

        $this->extension = new ContactInformationFieldsExtension($container);
    }

    protected function tearDown(): void
    {
        unset($this->extension);
        unset($this->helper);
    }

    public function testGetName()
    {
        $this->assertEquals(ContactInformationFieldsExtension::NAME, $this->extension->getName());
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
        $entity = '\stdClass';
        $contactInformation = [['name' => 'test']];

        $this->helper->expects($this->once())
            ->method('getEntityContactInformationFieldsInfo')
            ->with($entity)
            ->will($this->returnValue($contactInformation));

        $this->assertEquals(
            $contactInformation,
            self::callTwigFunction($this->extension, 'get_contact_information_fields_info', [$entity])
        );
    }
}
