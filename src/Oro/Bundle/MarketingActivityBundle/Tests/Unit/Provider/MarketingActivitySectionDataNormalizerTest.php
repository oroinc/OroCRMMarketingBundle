<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\Provider;

use Oro\Bundle\MarketingActivityBundle\Provider\MarketingActivitySectionDataNormalizer;

class MarketingActivitySectionDataNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetNormalizedData()
    {
        $normalizer = new MarketingActivitySectionDataNormalizer();

        $updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $items = [
            [
                'id' => 1,
                'campaignName' => "Test Campaign",
                'eventType' => 'Send',
                'eventDate' => '2017-01-20 20:20:00',
                'updatedAt' => $updatedAt,
                'createdAt' => $createdAt,
            ]
        ];

        $entityClass = 'Test';
        $entityId = 33;
        $result = $normalizer->getNormalizedData($items, $entityClass, $entityId);

        $expected = [
            'count' => 1,
            'data' => [
                [
                    'id' => 1,
                    'campaignName' => 'Test Campaign',
                    'eventType' => 'Send',
                    'eventDate' => '2017-01-20T20:20:00+00:00',
                    'updatedAt' => $updatedAt->format('c'),
                    'createdAt' => $createdAt->format('c'),
                    'relatedActivityClass' => 'Oro\\Bundle\\CampaignBundle\\Entity\\Campaign',
                    'relatedActivityId' => 1,
                    'editable' => false,
                    'removable' => false,
                    'targetEntityData' => [
                        'class' => 'Test',
                        'id' => 33,
                    ],
                ],
            ]
        ];
        $this->assertEquals($expected, $result);
    }
}
