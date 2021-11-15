<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\QueryDefinitionUtil;

class ContactInformationFieldsProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactInformationFieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $helper;

    /** @var ContactInformationFieldsProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->helper = $this->createMock(ContactInformationFieldHelper::class);

        $this->provider = new ContactInformationFieldsProvider($this->helper);
    }

    /**
     * @dataProvider queryFieldsDataProvider
     */
    public function testGetQueryTypedFields(
        ?array $contactInfoFields,
        string $definition,
        string $type,
        array $expected
    ) {
        $queryDesigner = $this->createMock(AbstractQueryDesigner::class);
        $queryDesigner->expects($this->any())
            ->method('getEntity')
            ->willReturn(\stdClass::class);
        $this->assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields);

        $this->assertEquals(
            $expected,
            $this->provider->getQueryTypedFields($queryDesigner, $type)
        );
    }

    private function assertGetQueryTypedFieldsCalls(
        AbstractQueryDesigner|\PHPUnit\Framework\MockObject\MockObject $queryDesigner,
        string $definition,
        ?array $contactInfoFields
    ): void {
        $queryDesigner->expects($this->any())
            ->method('getDefinition')
            ->willReturn($definition);

        $this->helper->expects($this->once())
            ->method('getEntityContactInformationFields')
            ->willReturn($contactInfoFields);
    }

    public function queryFieldsDataProvider(): array
    {
        return [
            [
                null,
                QueryDefinitionUtil::encodeDefinition([]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                [],
                QueryDefinitionUtil::encodeDefinition([]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                QueryDefinitionUtil::encodeDefinition([]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['email']
            ],
            [
                ['email' => 'email'],
                QueryDefinitionUtil::encodeDefinition(['columns' => [['name' => 'primaryEmail']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                QueryDefinitionUtil::encodeDefinition(['columns' => [['name' => 'primaryEmail']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                QueryDefinitionUtil::encodeDefinition(['columns' => [['name' => 'email']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE,
                []
            ],
            [
                ['email' => 'email'],
                QueryDefinitionUtil::encodeDefinition(['columns' => [['name' => 'email'], ['name' => 'phone']]]),
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['email']
            ],
        ];
    }

    /**
     * @dataProvider fieldsDataProvider
     */
    public function testGetEntityTypedFields(?array $contactInfoFields, string $type, array $expected)
    {
        $entity = \stdClass::class;
        $this->helper->expects($this->once())
            ->method('getEntityContactInformationFields')
            ->with($entity)
            ->willReturn($contactInfoFields);

        $this->assertEquals(
            $expected,
            $this->provider->getEntityTypedFields($entity, $type)
        );
    }

    public function testGetTypedFieldsValues()
    {
        $entity = new \stdClass();
        $entity->email = 'test';
        $entity->other = 'other';

        $expected = ['test'];
        $this->assertEquals($expected, $this->provider->getTypedFieldsValues(['email'], $entity));
    }

    public function fieldsDataProvider(): array
    {
        return [
            [
                null,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                [],
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                []
            ],
            [
                ['email' => 'email'],
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_PHONE,
                []
            ],
            [
                ['email' => 'email'],
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL,
                ['email']
            ],
        ];
    }

    /**
     * @dataProvider fieldsDataProvider
     */
    public function testGetMarketingListTypedFieldsManual(?array $contactInfoFields, string $type, array $expected)
    {
        $entity = \stdClass::class;
        $this->helper->expects($this->once())
            ->method('getEntityContactInformationFields')
            ->with($entity)
            ->willReturn($contactInfoFields);

        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(true);
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->willReturn(\stdClass::class);

        $this->assertEquals($expected, $this->provider->getMarketingListTypedFields($marketingList, $type));
    }

    /**
     * @dataProvider queryFieldsDataProvider
     */
    public function testGetMarketingListTypedFieldsNonManual(
        ?array $contactInfoFields,
        string $definition,
        string $type,
        array $expected
    ) {
        $queryDesigner = $this->createMock(AbstractQueryDesigner::class);
        $queryDesigner->expects($this->any())
            ->method('getEntity')
            ->willReturn(\stdClass::class);
        $marketingList = $this->createMock(MarketingList::class);
        $marketingList->expects($this->once())
            ->method('isManual')
            ->willReturn(false);
        $marketingList->expects($this->once())
            ->method('getSegment')
            ->willReturn($queryDesigner);
        $this->assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields);

        $this->assertEquals($expected, $this->provider->getMarketingListTypedFields($marketingList, $type));
    }
}
