<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Twig;

use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\MarketingListBundle\Twig\ContactInformationFieldsExtension;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContactInformationFieldsExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private ContactInformationFieldHelper&MockObject $helper;
    private ContactInformationFieldsExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->helper = $this->createMock(ContactInformationFieldHelper::class);

        $container = self::getContainerBuilder()
            ->add(ContactInformationFieldHelper::class, $this->helper)
            ->getContainer($this);

        $this->extension = new ContactInformationFieldsExtension($container);
    }

    public function testGetContactInformationFieldsInfoNoEntity()
    {
        $this->helper->expects(self::never())
            ->method($this->anything());

        self::assertEmpty(
            self::callTwigFunction($this->extension, 'get_contact_information_fields_info', [null])
        );
    }

    public function testGetContactInformationFieldsInfo()
    {
        $entity = \stdClass::class;
        $contactInformation = [['name' => 'test']];

        $this->helper->expects(self::once())
            ->method('getEntityContactInformationFieldsInfo')
            ->with($entity)
            ->willReturn($contactInformation);

        self::assertEquals(
            $contactInformation,
            self::callTwigFunction($this->extension, 'get_contact_information_fields_info', [$entity])
        );
    }
}
