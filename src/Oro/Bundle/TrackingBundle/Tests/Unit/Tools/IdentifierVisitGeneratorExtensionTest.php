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
    protected IdentifierVisitGeneratorExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new IdentifierVisitGeneratorExtension();
    }

    protected function tearDown(): void
    {
        unset($this->extension);
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports($schema, $expected)
    {
        static::assertEquals($expected, $this->extension->supports($schema));
    }

    public function supportsProvider()
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
                false
            ],
            [
                ['class' => 'Test\Entity', 'relation' => 'test'],
                false
            ]
        ];
    }

    public function testGenerate()
    {
        $schema = [
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
        ];

        $class = new ClassGenerator('Test\Entity');

        $this->extension->generate($schema, $class);
        $expectedCode = \file_get_contents(__DIR__ . '/Fixtures/generationIdentifierResult.txt');
        static::assertEquals(\trim($expectedCode), \trim($class->print()));
    }
}
