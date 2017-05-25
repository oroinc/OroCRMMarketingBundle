<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;

class ContactInformationFieldHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    /**
     * @var AbstractQueryDesigner|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryDesigner;

    /**
     * @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var EntityFieldProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldProvider;

    /**
     * @var ContactInformationFieldHelper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $fieldMappings = [
        'one' => [
            'fieldName'  => 'one',
            'columnName' => 'col1',
        ],
        'two' => [
            'fieldName'  => 'two',
            'columnName' => 'col2',
        ],
        'three' => [
            'fieldName'  => 'three',
            'columnName' => 'col3',
        ],
        'four' => [
            'fieldName'  => 'four',
            'columnName' => 'col4',
        ],
    ];

    protected function setUp()
    {
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryDesigner = $this
            ->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldProvider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityFieldProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new ContactInformationFieldHelper(
            $this->configProvider,
            $this->doctrineHelper,
            $this->fieldProvider
        );
    }

    public function testGetContactInformationFieldsNoDefinition()
    {
        $this->assertEmpty($this->helper->getQueryContactInformationFields($this->queryDesigner));
    }

    public function testGetContactInformationFieldsNoColumns()
    {
        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will($this->returnValue(json_encode(['columns' => []])));
        $this->assertEmpty($this->helper->getQueryContactInformationFields($this->queryDesigner));
    }

    public function testGetContactInformationFields()
    {
        $entity = \stdClass::class;

        $this->queryDesigner->expects($this->once())
            ->method('getDefinition')
            ->will(
                $this->returnValue(
                    json_encode(
                        [
                            'columns' => [
                                ['name' => 'one'],
                                ['name' => 'two'],
                                ['name' => 'three'],
                                ['name' => 'four']
                            ]
                        ]
                    )
                )
            );

        $this->queryDesigner->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));
        $this->fieldProvider->expects($this->any())
            ->method('getFields')
            ->will($this->returnValue([]));

        $this->assertContactInformationConfig($entity);

        $this->assertEquals(
            [
                'email' => [['name' => 'one']],
                'phone' => [['name' => 'two']]
            ],
            $this->helper->getQueryContactInformationFields($this->queryDesigner)
        );
    }

    /**
     * @param mixed $entity
     */
    protected function assertContactInformationConfig($entity)
    {
        $this->configProvider->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$entity, null, true],
                        [$entity, 'one', true],
                        [$entity, 'two', true],
                        [$entity, 'three', true],
                        [$entity, 'four', false]
                    ]
                )
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
        $this->configProvider->expects($this->atLeastOnce())
            ->method('getConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$entity, null, $entityConfig],
                        [$entity, 'one', $fieldNoInfoConfig],
                        [$entity, 'two', $fieldWithInfoConfig],
                        [$entity, 'three', $fieldNoInfoConfig]
                    ]
                )
            );
    }

    /**
     * @dataProvider fieldTypesDataProvider
     * @param string $field
     * @param string $expectedType
     */
    public function testGetContactInformationFieldType($field, $expectedType)
    {
        $entity = '\stdClass';
        $fields = [
            [
                'name' => 'one',
                'label' => 'One label'
            ],
            [
                'name' => 'two',
                'label' => 'Two label'
            ],
            [
                'name' => 'three',
                'label' => 'Three label'
            ],
            [
                'name' => 'four',
                'label' => 'Four label'
            ],
            [
                'name' => 'contactInformation',
                'label' => 'Contact information'
            ]
        ];
        $this->fieldProvider->expects($this->any())
            ->method('getFields')
            ->will($this->returnValue($fields));
        $this->assertContactInformationConfig($entity);
        $this->assertEquals($expectedType, $this->helper->getContactInformationFieldType($entity, $field));
    }

    /**
     * @return array
     */
    public function fieldTypesDataProvider()
    {
        return [
            ['one', 'email'],
            ['two', 'phone'],
            ['three', null],
            ['four', null],
            ['contactInformation', 'contactInformation'],
        ];
    }

    public function testGetEntityContactInformationFields()
    {
        $entity = '\stdClass';
        $fields = [
            [
                'name' => 'one',
                'label' => 'One label'
            ],
            [
                'name' => 'two',
                'label' => 'Two label'
            ],
            [
                'name' => 'three',
                'label' => 'Three label'
            ],
            [
                'name' => 'four',
                'label' => 'Four label'
            ]
        ];
        $this->fieldProvider->expects($this->exactly(2))
            ->method('getFields')
            ->with($entity, false, true)
            ->will($this->returnValue($fields));

        $this->assertContactInformationConfig($entity);
        $this->assertEquals(
            ['one' => 'email', 'two' => 'phone'],
            $this->helper->getEntityContactInformationFields($entity)
        );
    }

    public function testGetEntityContactInformationFieldsInfo()
    {
        $entity = '\stdClass';

        $this->assertContactInformationConfig($entity);

        $fields = [
            [
                'name' => 'one',
                'label' => 'One label'
            ],
            [
                'name' => 'two',
                'label' => 'Two label'
            ],
            [
                'name' => 'three',
                'label' => 'Three label'
            ],
            [
                'name' => 'four',
                'label' => 'Four label'
            ]
        ];
        $this->fieldProvider->expects($this->exactly(3))
            ->method('getFields')
            ->with($entity, false, true)
            ->will($this->returnValue($fields));
        $this->assertEquals(
            [
                [
                    'name' => 'one',
                    'label' => 'One label',
                    'contact_information_type' => 'email'
                ],
                [
                    'name' => 'two',
                    'label' => 'Two label',
                    'contact_information_type' => 'phone'
                ]
            ],
            $this->helper->getEntityContactInformationFieldsInfo($entity)
        );
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfig($key, $data)
    {
        $config = $this->getMockForAbstractClass('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $config->expects($this->any())
            ->method('get')
            ->with($key)
            ->will($this->returnValue($data));

        return $config;
    }
}
