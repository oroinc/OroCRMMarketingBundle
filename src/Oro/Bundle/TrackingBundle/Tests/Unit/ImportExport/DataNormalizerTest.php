<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\ImportExport;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\TrackingBundle\Entity\TrackingData;
use Oro\Bundle\TrackingBundle\ImportExport\DataNormalizer;
use Oro\Component\Testing\ReflectionUtil;

class DataNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /** @var FieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldHelper;

    /** @var DataNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->fieldHelper = $this->createMock(FieldHelper::class);

        $this->normalizer = new DataNormalizer($this->fieldHelper);
    }

    public function testSupportsNormalization(): void
    {
        self::assertFalse(
            $this->normalizer->supportsNormalization([])
        );
    }

    public function testNormalize(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not implemented');

        $this->normalizer->normalize(new \stdClass());
    }

    /**
     * @dataProvider supportProvider
     */
    public function testSupportsDenormalization(array $data, string $class, string $passedClass, bool $result): void
    {
        $this->normalizer->setEntityName($class);

        self::assertEquals(
            $result,
            $this->normalizer->supportsDenormalization($data, $passedClass)
        );
    }

    public function supportProvider(): array
    {
        return [
            [
                [],
                \stdClass::class,
                \stdClass::class,
                true
            ],
            [
                [],
                \stdClass::class,
                'Namespace\Entity',
                false
            ]
        ];
    }

    public function testDenormalize(): void
    {
        $this->fieldHelper->expects(self::once())
            ->method('getEntityFields')
            ->willReturn([]);

        $result = $this->normalizer->denormalize(
            [],
            TrackingData::class
        );

        self::assertInstanceOf(
            TrackingData::class,
            $result
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testUpdateData(array $data, array $expected): void
    {
        self::assertEquals(
            $expected,
            ReflectionUtil::callMethod($this->normalizer, 'updateData', [$data])
        );
    }

    public function dataProvider(): array
    {
        $data = [
            'website'        => 'id1',
            'name'           => 'name',
            'value'          => 100,
            'userIdentifier' => 'userIdentifier',
        ];

        return [
            [
                [],
                [
                    'data'  => json_encode([]),
                    'event' => [
                        'name'  => DataNormalizer::DEFAULT_NAME,
                        'value' => 1
                    ]
                ]
            ],
            [
                $data,
                [
                    'data'  => json_encode($data),
                    'event' => [
                        'website'        => [
                            'identifier' => 'id1',
                        ],
                        'name'           => 'name',
                        'value'          => 100,
                        'userIdentifier' => 'userIdentifier',
                    ]
                ]
            ],
            [
                [
                    'website'        => 'id1',
                    'userIdentifier' => 'userIdentifier%20%3D',
                    'title'          => 'title%20%3D',
                ],
                [
                    'data'  => json_encode([
                        'website'        => 'id1',
                        'userIdentifier' => 'userIdentifier =',
                        'title'          => 'title =',
                    ]),
                    'event' => [
                        'website'        => [
                            'identifier' => 'id1',
                        ],
                        'userIdentifier' => 'userIdentifier =',
                        'title'          => 'title =',
                        'name'           => 'visit',
                        'value'          => 1,
                    ]
                ]
            ]
        ];
    }
}
