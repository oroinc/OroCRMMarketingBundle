<?php

namespace Oro\Bundle\MarketingActivityBundle\Provider;

class MarketingActivitySectionDataNormalizer
{
    /**
     * @param $items
     *
     * @return array
     */
    public function getNormalizedData($items)
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = $this->normalizeItem($item);
        }

        return [
            'count' => count($result),
            'data' => $result
        ];
    }

    /**
     * @param $item
     *
     * @return array
     */
    protected function normalizeItem($item)
    {
        $resultItem = [];

        foreach ($item as $field => $value) {
            if ($value instanceof \DateTime) {
                $value = $value->format('c');
            }
            if ($field == 'eventDate') {
                $value = date_create($value, new \DateTimeZone('UTC'))->format('c');
            }
            $resultItem[$field] = $value;
        }

        $resultItem = $this->applyAdditionalData($resultItem);

        return $resultItem;
    }

    /**
     * @param $item
     *
     * @return array
     */
    protected function applyAdditionalData($item)
    {
        return array_merge(
            $item,
            [
                'relatedActivityClass' => 'Oro\Bundle\CampaignBundle\Entity\Campaign',
                'relatedActivityId' => $item['id'],
                'editable' => false,
                'removable' => false,
            ]
        );
    }
}
