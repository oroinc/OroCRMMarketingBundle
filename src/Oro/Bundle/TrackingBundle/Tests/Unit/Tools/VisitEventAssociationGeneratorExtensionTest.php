<?php
declare(strict_types=1);

namespace Oro\Bundle\TrackingBundle\Tests\Unit\Tools;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingVisitEvent;
use Oro\Bundle\TrackingBundle\Migration\Extension\VisitEventAssociationExtension;
use Oro\Bundle\TrackingBundle\Tools\VisitEventAssociationGeneratorExtension;
use Oro\Component\PhpUtils\ClassGenerator;

class VisitEventAssociationGeneratorExtensionTest extends \PHPUnit\Framework\TestCase
{
    private VisitEventAssociationGeneratorExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new VisitEventAssociationGeneratorExtension();
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
                    'class' => TrackingVisitEvent::class,
                    'relation' => 'test',
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                TrackingVisitEvent::class,
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity',
                                    VisitEventAssociationExtension::ASSOCIATION_KIND
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
                    'class' => TrackingVisitEvent::class,
                    'relation' => 'test',
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                TrackingVisitEvent::class,
                                'testField',
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity',
                            'state' => 'Active'
                        ]
                    ]
                ],
                false
            ],
            [
                [
                    'class' => TrackingVisitEvent::class,
                    'relation' => 'test',
                    'relationData' => [
                        [
                            'field_id' => new FieldConfigId(
                                'extend',
                                TrackingVisitEvent::class,
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity',
                                    VisitEventAssociationExtension::ASSOCIATION_KIND
                                ),
                                'manyToMany'
                            ),
                            'target_entity' => 'Test\TargetEntity',
                            'state' => 'Active'
                        ]
                    ]
                ],
                false
            ],
            [
                ['class' => TrackingVisitEvent::class],
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

        $expectedBody = \file_get_contents(__DIR__ . $expectedResultFileName);
        self::assertEquals(\trim($expectedBody), \trim($class->print()));
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
                                    VisitEventAssociationExtension::ASSOCIATION_KIND
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
                                    VisitEventAssociationExtension::ASSOCIATION_KIND
                                ),
                                'manyToOne'
                            ),
                            'target_entity' => 'Test\TargetEntity2',
                            'state' => 'Active'
                        ],
                        [   // should be ignored because field type is not multipleManyToOne
                            'field_id' => new FieldConfigId(
                                'extend',
                                'Test\Entity',
                                ExtendHelper::buildAssociationName(
                                    'Test\TargetEntity3',
                                    VisitEventAssociationExtension::ASSOCIATION_KIND
                                ),
                                'manyToMany'
                            ),
                            'target_entity' => 'Test\TargetEntity3',
                            'state' => 'Active'
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
                'expectedResultFileName' => '/Fixtures/generationAssociationResult.txt',
            ],
            'only default association methods' => [
                'schema' => [],
                'expectedResultFileName' => '/Fixtures/generationDefaultAssociationMethodsResult.txt',
            ],
        ];
    }
}
