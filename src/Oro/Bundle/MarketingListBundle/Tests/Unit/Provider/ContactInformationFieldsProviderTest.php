<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Provider;

use Oro\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\QueryDefinitionUtil;

class ContactInformationFieldsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContactInformationFieldsProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    protected function setUp(): void
    {
        $this->helper = $this
            ->getMockBuilder('Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ContactInformationFieldsProvider($this->helper);
    }

    /**
     * @param array $contactInfoFields
     * @param array $definition
     * @param string $type
     * @param array $expected
     *
     * @dataProvider queryFieldsDataProvider
     */
    public function testGetQueryTypedFields($contactInfoFields, $definition, $type, $expected)
    {
        $queryDesigner = $this->getMockBuilder('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryDesigner->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $this->assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields);

        $this->assertEquals(
            $expected,
            $this->provider->getQueryTypedFields($queryDesigner, $type)
        );
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject$queryDesigner
     * @param array $definition
     * @param array $contactInfoFields
     */
    protected function assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields)
    {
        $queryDesigner
            ->expects($this->any())
            ->method('getDefinition')
            ->will($this->returnValue($definition));

        $this->helper
            ->expects($this->once())
            ->method('getEntityContactInformationFields')
            ->will($this->returnValue($contactInfoFields));
    }

    /**
     * @return array
     */
    public function queryFieldsDataProvider()
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
     * @param array $contactInfoFields
     * @param string $type
     * @param array $expected
     *
     * @dataProvider fieldsDataProvider
     */
    public function testGetEntityTypedFields($contactInfoFields, $type, $expected)
    {
        $entity = '\stdClass';
        $this->assertGetEntityTypedFieldsCalls($entity, $contactInfoFields);

        $this->assertEquals(
            $expected,
            $this->provider->getEntityTypedFields($entity, $type)
        );
    }

    protected function assertGetEntityTypedFieldsCalls($entity, $contactInfoFields)
    {
        $this->helper
            ->expects($this->once())
            ->method('getEntityContactInformationFields')
            ->with($entity)
            ->will($this->returnValue($contactInfoFields));
    }

    public function testGetTypedFieldsValues()
    {
        $entity = new \stdClass();
        $entity->email = 'test';
        $entity->other = 'other';

        $expected = ['test'];
        $this->assertEquals($expected, $this->provider->getTypedFieldsValues(['email'], $entity));
    }

    /**
     * @return array
     */
    public function fieldsDataProvider()
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
     * @param array $contactInfoFields
     * @param string $type
     * @param array $expected
     *
     * @dataProvider fieldsDataProvider
     */
    public function testGetMarketingListTypedFieldsManual($contactInfoFields, $type, $expected)
    {
        $entity = '\stdClass';
        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(true));
        $marketingList->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $this->assertGetEntityTypedFieldsCalls($entity, $contactInfoFields);

        $this->assertEquals($expected, $this->provider->getMarketingListTypedFields($marketingList, $type));
    }

    /**
     * @param array $contactInfoFields
     * @param array $definition
     * @param string $type
     * @param array $expected
     *
     * @dataProvider queryFieldsDataProvider
     */
    public function testGetMarketingListTypedFieldsNonManual($contactInfoFields, $definition, $type, $expected)
    {
        $queryDesigner = $this->getMockBuilder('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $queryDesigner->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('\stdClass'));
        $marketingList = $this->getMockBuilder('Oro\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingList->expects($this->once())
            ->method('isManual')
            ->will($this->returnValue(false));
        $marketingList->expects($this->once())
            ->method('getSegment')
            ->will($this->returnValue($queryDesigner));
        $this->assertGetQueryTypedFieldsCalls($queryDesigner, $definition, $contactInfoFields);

        $this->assertEquals($expected, $this->provider->getMarketingListTypedFields($marketingList, $type));
    }
}
