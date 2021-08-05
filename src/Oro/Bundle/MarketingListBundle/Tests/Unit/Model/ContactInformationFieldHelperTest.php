<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\QueryDefinitionUtil;

class ContactInformationFieldHelperTest extends \PHPUnit\Framework\TestCase
{
    private ConfigProvider|\PHPUnit\Framework\MockObject\MockObject $configProvider;

    private AbstractQueryDesigner|\PHPUnit\Framework\MockObject\MockObject $queryDesigner;

    private DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper;

    private EntityFieldProvider|\PHPUnit\Framework\MockObject\MockObject $fieldProvider;

    private ContactInformationFieldHelper $helper;

    private array $fieldMappings = [
        'one' => [
            'fieldName' => 'one',
            'columnName' => 'col1',
        ],
        'two' => [
            'fieldName' => 'two',
            'columnName' => 'col2',
        ],
        'three' => [
            'fieldName' => 'three',
            'columnName' => 'col3',
        ],
        'four' => [
            'fieldName' => 'four',
            'columnName' => 'col4',
        ],
    ];

    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->queryDesigner = $this->createMock(AbstractQueryDesigner::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->fieldProvider = $this->createMock(EntityFieldProvider::class);

        $this->helper = new ContactInformationFieldHelper(
            $this->configProvider,
            $this->doctrineHelper,
            $this->fieldProvider
        );
    }

    public function testGetContactInformationFieldsNoDefinition(): void
    {
        self::assertEmpty($this->helper->getQueryContactInformationFields($this->queryDesigner));
    }

    public function testGetContactInformationFieldsNoColumns(): void
    {
        $this->queryDesigner->expects(self::once())
            ->method('getDefinition')
            ->willReturn(QueryDefinitionUtil::encodeDefinition(['columns' => []]));
        self::assertEmpty($this->helper->getQueryContactInformationFields($this->queryDesigner));
    }

    public function testGetContactInformationFields(): void
    {
        $entity = \stdClass::class;

        $this->queryDesigner->expects(self::once())
            ->method('getDefinition')
            ->willReturn(
                QueryDefinitionUtil::encodeDefinition(
                    [
                        'columns' => [
                            ['name' => 'one'],
                            ['name' => 'two'],
                            ['name' => 'three'],
                            ['name' => 'four'],
                        ],
                    ]
                )
            );

        $this->queryDesigner->expects(self::once())
            ->method('getEntity')
            ->willReturn($entity);
        $this->fieldProvider->expects(self::any())
            ->method('getEntityFields')
            ->willReturn([]);

        $this->assertContactInformationConfig($entity);

        self::assertEquals(
            [
                'email' => [['name' => 'one']],
                'phone' => [['name' => 'two']],
            ],
            $this->helper->getQueryContactInformationFields($this->queryDesigner)
        );
    }

    /**
     * @param mixed $entity
     */
    private function assertContactInformationConfig($entity): void
    {
        $this->configProvider->expects(self::atLeastOnce())
            ->method('hasConfig')
            ->willReturnMap(
                [
                    [$entity, null, true],
                    [$entity, 'one', true],
                    [$entity, 'two', true],
                    [$entity, 'three', true],
                    [$entity, 'four', false],
                    [$entity, 'contactInformation', false],
                ]
            );

        $entityConfig = $this->getConfig(
            'contact_information',
            ['email' => [['fieldName' => 'one']]]
        );
        $fieldWithInfoConfig = $this->getConfig(
            'contact_information',
            'phone'
        );
        $fieldNoInfoConfig = $this->getConfig(
            'contact_information',
            null
        );
        $this->configProvider->expects(self::atLeastOnce())
            ->method('getConfig')
            ->willReturnMap(
                [
                    [$entity, null, $entityConfig],
                    [$entity, 'one', $fieldNoInfoConfig],
                    [$entity, 'two', $fieldWithInfoConfig],
                    [$entity, 'three', $fieldNoInfoConfig],
                ]
            );
    }

    /**
     * @dataProvider fieldTypesDataProvider
     *
     * @param string $field
     * @param string $expectedType
     */
    public function testGetContactInformationFieldType($field, $expectedType): void
    {
        $entity = \stdClass::class;
        $fields = [
            [
                'name' => 'one',
                'label' => 'One label',
            ],
            [
                'name' => 'two',
                'label' => 'Two label',
            ],
            [
                'name' => 'three',
                'label' => 'Three label',
            ],
            [
                'name' => 'four',
                'label' => 'Four label',
            ],
            [
                'name' => 'contactInformation',
                'label' => 'Contact information',
            ],
        ];
        $this->fieldProvider->expects(self::any())
            ->method('getEntityFields')
            ->willReturn($fields);
        $this->assertContactInformationConfig($entity);
        self::assertEquals($expectedType, $this->helper->getContactInformationFieldType($entity, $field));
    }

    /**
     * @return array
     */
    public function fieldTypesDataProvider(): array
    {
        return [
            ['one', 'email'],
            ['two', 'phone'],
            ['three', null],
            ['four', null],
            ['contactInformation', 'contactInformation'],
        ];
    }

    public function testGetEntityContactInformationFields(): void
    {
        $entity = \stdClass::class;
        $fields = [
            [
                'name' => 'one',
                'label' => 'One label',
            ],
            [
                'name' => 'two',
                'label' => 'Two label',
            ],
            [
                'name' => 'three',
                'label' => 'Three label',
            ],
            [
                'name' => 'four',
                'label' => 'Four label',
            ],
        ];
        $this->fieldProvider->expects(self::exactly(2))
            ->method('getEntityFields')
            ->with(
                $entity,
                EntityFieldProvider::OPTION_WITH_VIRTUAL_FIELDS
                | EntityFieldProvider::OPTION_APPLY_EXCLUSIONS
            )
            ->willReturn($fields);

        $this->assertContactInformationConfig($entity);
        self::assertEquals(
            ['one' => 'email', 'two' => 'phone'],
            $this->helper->getEntityContactInformationFields($entity)
        );
    }

    public function testGetEntityContactInformationFieldsInfo(): void
    {
        $entity = \stdClass::class;

        $this->assertContactInformationConfig($entity);

        $fields = [
            [
                'name' => 'one',
                'label' => 'One label',
            ],
            [
                'name' => 'two',
                'label' => 'Two label',
            ],
            [
                'name' => 'three',
                'label' => 'Three label',
            ],
            [
                'name' => 'four',
                'label' => 'Four label',
            ],
        ];
        $this->fieldProvider->expects(self::exactly(3))
            ->method('getEntityFields')
            ->withConsecutive(
                [
                    $entity,
                    EntityFieldProvider::OPTION_WITH_VIRTUAL_FIELDS | EntityFieldProvider::OPTION_APPLY_EXCLUSIONS,
                ],
                [
                    $entity,
                    EntityFieldProvider::OPTION_WITH_VIRTUAL_FIELDS | EntityFieldProvider::OPTION_APPLY_EXCLUSIONS,
                ],
                [
                    $entity,
                    EntityFieldProvider::OPTION_WITH_VIRTUAL_FIELDS
                    | EntityFieldProvider::OPTION_APPLY_EXCLUSIONS
                    | EntityFieldProvider::OPTION_TRANSLATE,
                ]
            )
            ->willReturn($fields);
        self::assertEquals(
            [
                [
                    'name' => 'one',
                    'label' => 'One label',
                    'contact_information_type' => 'email',
                ],
                [
                    'name' => 'two',
                    'label' => 'Two label',
                    'contact_information_type' => 'phone',
                ],
            ],
            $this->helper->getEntityContactInformationFieldsInfo($entity)
        );
    }

    /**
     * @param string $key
     * @param mixed $data
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getConfig($key, $data): \PHPUnit\Framework\MockObject\MockObject
    {
        $config = $this->createMock(ConfigInterface::class);
        $config->expects(self::any())
            ->method('get')
            ->with($key)
            ->willReturn($data);

        return $config;
    }
}
