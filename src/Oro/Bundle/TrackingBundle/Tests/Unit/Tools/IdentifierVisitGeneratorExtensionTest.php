<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Tools;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisit;
use Oro\Bundle\TrackingBundle\Migration\Extension\IdentifierEventExtension;
use Oro\Bundle\TrackingBundle\Tools\IdentifierVisitGeneratorExtension;
use Oro\Component\PhpUtils\ClassGenerator;

class IdentifierVisitGeneratorExtensionTest extends \PHPUnit\Framework\TestCase
{
    private IdentifierVisitGeneratorExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new IdentifierVisitGeneratorExtension();
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(array $schema, bool $expected)
    {
        self::assertEquals($expected, $this->extension->supports($schema));
    }

    public function supportsProvider(): array
    {
        return [
            [
                [
                    'class' => TrackingVisit::class,
                    'relation' => 'test',
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                TrackingVisit::class,
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity',
                                    IdentifierEventExtension::ASSOCIATION_KIND
                                ),
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity',
                            'state' => 'Active'
                        ]
                    ]
                ],
                true
            ],
            [
                [
                    'class' => TrackingVisit::class,
                    'relation' => 'test',
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                TrackingVisit::class,
                                'testField',
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity'
                        ]
                    ]
                ],
                false
            ],
            [
                [
                    'class' => TrackingVisit::class,
                    'relation' => 'test',
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                TrackingVisit::class,
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity',
                                    IdentifierEventExtension::ASSOCIATION_KIND
                                ),
                                'manyToMany'
                            ),
                            'target_entity' => 'Test\TargetEntity'
                        ]
                    ]
                ],
                false
            ],
            [
                ['class' => TrackingVisit::class],
                true
            ],
            [
                ['class' => 'Test\Entity', 'relation' => 'test'],
                false
            ]
        ];
    }

    /**
     * @dataProvider getGenerateDataProvider
     */
    public function testGenerate(array $schema, string $expectedResultFileName): void
    {
        $class = new ClassGenerator('Test\Entity');

        $this->extension->generate($schema, $class);
        $expectedCode = \file_get_contents(__DIR__ . $expectedResultFileName);
        self::assertEquals(\trim($expectedCode), \trim($class->print()));
    }

    public function getGenerateDataProvider(): array
    {
        return [
            'associations' => [
                'schema' => [
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                'Test\Entity',
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity1',
                                    IdentifierEventExtension::ASSOCIATION_KIND
                                ),
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity1',
                            'state' => 'Active'
                        ],
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                'Test\Entity',
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity2',
                                    IdentifierEventExtension::ASSOCIATION_KIND
                                ),
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity2',
                            'state' => 'Active'
                        ],
                        [   // should be ignored because field type is not manyToOne
                            'field_id' => new FieldConfigId(
                                'extend',
                                'Test\Entity',
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity3',
                                    IdentifierEventExtension::ASSOCIATION_KIND
                                ),
                                'manyToMany'
                            ),
                            'target_entity' => 'Test\TargetEntity3'
                        ],
                        [   // should be ignored because field name is not match association naming conventions
                            'field_id' => new FieldConfigId(
                                'extend',
                                'Test\Entity',
                                'testField',
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity4',
                            'state' => 'Active'
                        ]
                    ]
                ],
                'expectedResultFileName' => '/Fixtures/generationIdentifierResult.txt',
            ],
            'only default association methods' => [
                'schema' => [],
                'expectedResultFileName' => '/Fixtures/generationIdentifierDefaultAssociationMethodsResult.txt',
            ],
        ];
    }
}
