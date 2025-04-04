<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\VirtualFieldProviderInterface;

/**
 * The provider to get marketing list item virtual fields.
 */
class MarketingListItemVirtualFieldProvider implements VirtualFieldProviderInterface
{
    const FIELD_CONTACTED_TIMES = 'mlContactedTimes';
    const FIELD_LAST_CONTACTED_AT = 'mlLastContactedAt';

    /**
     * @var array|null
     */
    protected $marketingListByEntity;

    /**
     * @var MarketingListVirtualRelationProvider
     */
    protected $relationProvider;

    public function __construct(MarketingListVirtualRelationProvider $relationProvider)
    {
        $this->relationProvider = $relationProvider;
    }

    #[\Override]
    public function isVirtualField($className, $fieldName)
    {
        return
            (self::FIELD_CONTACTED_TIMES === $fieldName || self::FIELD_LAST_CONTACTED_AT === $fieldName)
            && $this->relationProvider->hasMarketingList($className);
    }

    #[\Override]
    public function getVirtualFieldQuery($className, $fieldName)
    {
        if ($fieldName === self::FIELD_LAST_CONTACTED_AT) {
            return $this->getLastContactedAtFieldQuery($className);
        } elseif ($fieldName === self::FIELD_CONTACTED_TIMES) {
            return $this->getContactedTimesFieldQuery($className);
        }

        throw new \RuntimeException(sprintf('No virtual field found for %s::%s', $className, $fieldName));
    }

    #[\Override]
    public function getVirtualFields($className)
    {
        if ($this->relationProvider->hasMarketingList($className)) {
            return [self::FIELD_CONTACTED_TIMES, self::FIELD_LAST_CONTACTED_AT];
        }

        return [];
    }

    /**
     * @param string $className
     * @return array
     */
    protected function getContactedTimesFieldQuery($className)
    {
        $relationData = $this->relationProvider->getRelationDefinition($className);
        $itemAlias = MarketingListVirtualRelationProvider::MARKETING_LIST_ITEM_RELATION_NAME;

        return [
            'select' => [
                'expr' => $itemAlias . '.contactedTimes',
                'label' => 'oro.marketinglist.marketinglistitem.contacted_times.label',
                'return_type' => 'integer'
            ],
            'join' => $relationData['query']['join']
        ];
    }

    /**
     * @param string $className
     * @return array
     */
    protected function getLastContactedAtFieldQuery($className)
    {
        $relationData = $this->relationProvider->getRelationDefinition($className);
        $itemAlias = MarketingListVirtualRelationProvider::MARKETING_LIST_ITEM_RELATION_NAME;

        return [
            'select' => [
                'expr' => $itemAlias . '.lastContactedAt',
                'label' => 'oro.marketinglist.marketinglistitem.last_contacted_at.label',
                'return_type' => 'datetime'
            ],
            'join' => $relationData['query']['join']
        ];
    }
}
