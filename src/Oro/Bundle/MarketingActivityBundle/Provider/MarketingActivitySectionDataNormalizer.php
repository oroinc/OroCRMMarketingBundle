<?php

namespace Oro\Bundle\MarketingActivityBundle\Provider;

class MarketingActivitySectionDataNormalizer
{
    /**
     * @param array   $items
     * @param string  $entityClass
     * @param integer $entityId
     *
     * @return array
     */
    public function getNormalizedData($items, $entityClass, $entityId)
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = $this->normalizeItem($item, $entityClass, $entityId);
        }

        return [
            'count' => count($result),
            'data' => $result
        ];
    }

    /**
     * @param array   $item
     * @param string  $entityClass
     * @param integer $entityId
     *
     * @return array
     */
    protected function normalizeItem($item, $entityClass, $entityId)
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
        $resultItem = $this->applyTargetEntityData($resultItem, $entityClass, $entityId);

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

    /**
     * @param array   $item
     * @param string  $entityClass
     * @param integer $entityId
     *
     * @return array
     */
    protected function applyTargetEntityData($item, $entityClass, $entityId)
    {
        return array_merge(
            $item,
            [
                'targetEntityData' => [
                    'class' => $entityClass,
                    'id' => $entityId
                ]
            ]
        );
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function getCampaignFilterValues($items)
    {
        $result = [];

        foreach ($items as $item) {
            $result[$item['id']] = $item['campaignName'];
        }

        return $result;
    }
}
