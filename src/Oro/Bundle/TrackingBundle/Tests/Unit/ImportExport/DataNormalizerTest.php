<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\ImportExport;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
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

    public function testSupportsNormalization()
    {
        $this->assertFalse(
            $this->normalizer->supportsNormalization([])
        );
    }

    public function testNormalize()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not implemented');

        $this->normalizer->normalize(new \stdClass());
    }

    /**
     * @param array  $data
     * @param string $class
     * @param string $passedClass
     * @param bool   $result
     *
     * @dataProvider supportProvider
     */
    public function testSupportsDenormalization(array $data, $class, $passedClass, $result)
    {
        $this->normalizer->setEntityName($class);

        $this->assertEquals(
            $result,
            $this->normalizer->supportsDenormalization($data, $passedClass)
        );
    }

    /**
     * @return array
     */
    public function supportProvider()
    {
        return [
            [
                [],
                '\stdClass',
                '\stdClass',
                true
            ],
            [
                [],
                '\stdClass',
                'Namespace\Entity',
                false
            ]
        ];
    }

    public function testDenormalize()
    {
        $this->fieldHelper
            ->expects($this->once())
            ->method('getFields')
            ->will($this->returnValue([]));

        $result = $this->normalizer->denormalize(
            [],
            'Oro\Bundle\TrackingBundle\Entity\TrackingData'
        );

        $this->assertInstanceOf(
            'Oro\Bundle\TrackingBundle\Entity\TrackingData',
            $result
        );
    }

    /**
     * @param array $data
     * @param array $expected
     *
     * @dataProvider dataProvider
     */
    public function testUpdateData(array $data, array $expected)
    {
        $this->assertEquals(
            $expected,
            ReflectionUtil::callMethod($this->normalizer, 'updateData', [$data])
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
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
