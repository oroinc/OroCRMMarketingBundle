<?php

namespace Oro\Bundle\MarketingActivityBundle\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;
use Oro\Bundle\MarketingActivityBundle\Entity\MarketingActivity;

/**
 * Provides "marketingActivity" virtual relation.
 */
class MarketingActivityVirtualRelationProvider implements VirtualRelationProviderInterface
{
    private const RELATION_NAME = 'marketingActivity';

    private EntityProvider $entityProvider;
    private array $marketingActivityByEntity = [];

    public function __construct(EntityProvider $entityProvider)
    {
        $this->entityProvider = $entityProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isVirtualRelation($className, $fieldName)
    {
        return
            self::RELATION_NAME === $fieldName
            && $this->hasMarketingActivity($className);
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelationQuery($className, $fieldName)
    {
        $relations = $this->getVirtualRelations($className);
        if (\array_key_exists($fieldName, $relations)) {
            return $relations[$fieldName]['query'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelations($className)
    {
        if ($this->hasMarketingActivity($className)) {
            return [self::RELATION_NAME => $this->getRelationDefinition($className)];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetJoinAlias($className, $fieldName, $selectFieldName = null)
    {
        return self::RELATION_NAME;
    }

    /**
     * Checks whether marketing activities are enabled for the given entity.
     */
    private function hasMarketingActivity(string $className): bool
    {
        if (!isset($this->marketingActivityByEntity[$className])) {
            $this->marketingActivityByEntity[$className] = !$this->entityProvider->isIgnoredEntity($className);
        }

        return $this->marketingActivityByEntity[$className];
    }

    private function getRelationDefinition(string $className): array
    {
        return [
            'label' => 'oro.marketingactivity.entity_label',
            'relation_type' => 'oneToMany',
            'related_entity_name' => MarketingActivity::class,
            'query' => [
                'join' => [
                    'left' => [
                        [
                            'join' => MarketingActivity::class,
                            'alias' => self::RELATION_NAME,
                            'conditionType' => Join::WITH,
                            'condition' => self::RELATION_NAME . ".entityClass = '" . $className . "'"
                                . ' AND ' .  self::RELATION_NAME . '.entityId = entity.id'
                        ]
                    ]
                ]
            ]
        ];
    }
}
