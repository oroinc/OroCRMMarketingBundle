<?php

namespace Oro\Bundle\TrackingBundle\Tests\Unit\ImportExport;

use Oro\Bundle\TrackingBundle\ImportExport\DataNormalizer;

class DataNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataNormalizer
     */
    protected $normalizer;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $fieldHelper;

    protected function setUp(): void
    {
        $this->fieldHelper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\Helper\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

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
     * @dataProvider dataProvider
     */
    public function testUpdateData(array $data, array $expected)
    {
        $reflectionClass = new \ReflectionClass($this->normalizer);
        $method          = $reflectionClass->getMethod('updateData');
        $method->setAccessible(true);

        $this->assertEquals(
            $expected,
            $method->invokeArgs($this->normalizer, ['data' => $data])
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
