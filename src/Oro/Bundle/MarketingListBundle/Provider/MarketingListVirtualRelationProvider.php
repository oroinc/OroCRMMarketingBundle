<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Provides virtual relations between an entity and a marketing list that is based on this entity.
 */
class MarketingListVirtualRelationProvider implements VirtualRelationProviderInterface
{
    private const RELATION_NAME = 'marketingList_virtual';
    public const MARKETING_LIST_ITEM_RELATION_NAME = 'marketingListItems';
    private const MARKETING_LIST_BY_ENTITY_CACHE_KEY = 'oro_marketing_list.lists_by_entities';

    private DoctrineHelper $doctrineHelper;
    private CacheInterface $cacheProvider;

    public function __construct(DoctrineHelper $doctrineHelper, CacheInterface $cacheProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->cacheProvider = $cacheProvider;
    }

    public function isVirtualRelation($className, $fieldName): bool
    {
        return
            $fieldName === self::RELATION_NAME
            && $this->hasMarketingList($className);
    }

    public function getVirtualRelationQuery($className, $fieldName): array
    {
        $relations = $this->getVirtualRelations($className);
        if (array_key_exists($fieldName, $relations)) {
            return $relations[$fieldName]['query'];
        }

        return [];
    }

    public function getVirtualRelations($className): array
    {
        if ($this->hasMarketingList($className)) {
            return [self::RELATION_NAME => $this->getRelationDefinition($className)];
        }

        return [];
    }

    public function getTargetJoinAlias($className, $fieldName, $selectFieldName = null): string
    {
        $isItemField = in_array(
            $selectFieldName,
            [
                rtrim(self::MARKETING_LIST_ITEM_RELATION_NAME, 's'),
                self::MARKETING_LIST_ITEM_RELATION_NAME,
            ]
        );
        if ($isItemField) {
            return self::MARKETING_LIST_ITEM_RELATION_NAME;
        }

        return self::RELATION_NAME;
    }

    public function hasMarketingList(string $className): bool
    {
        $marketingListByEntity = $this->cacheProvider->get(static::MARKETING_LIST_BY_ENTITY_CACHE_KEY, function () {
            $marketingListByEntity = [];
            $repository = $this->doctrineHelper->getEntityRepository(MarketingList::class);
            $qb = $repository->createQueryBuilder('ml')
                ->select('ml.entity')
                ->distinct();
            $entities = $qb->getQuery()->getArrayResult();
            foreach ($entities as $entity) {
                $marketingListByEntity[$entity['entity']] = true;
            }
            return $marketingListByEntity;
        });

        return !empty($marketingListByEntity[$className]);
    }

    public function getRelationDefinition(string $className): array
    {
        $idField = $this->doctrineHelper->getSingleEntityIdentifierFieldName($className);

        return [
            'label' => 'oro.marketinglist.entity_label',
            'relation_type' => 'oneToMany',
            'related_entity_name' => 'Oro\Bundle\MarketingListBundle\Entity\MarketingList',
            'query' => [
                'join' => [
                    'left' => [
                        [
                            'join' => 'OroMarketingListBundle:MarketingListItem',
                            'alias' => self::MARKETING_LIST_ITEM_RELATION_NAME,
                            'conditionType' => Join::WITH,
                            'condition' => 'entity.' . $idField
                                    . ' = ' . self::MARKETING_LIST_ITEM_RELATION_NAME . '.entityId'
                        ],
                        [
                            'join' => 'OroMarketingListBundle:MarketingList',
                            'alias' => self::RELATION_NAME,
                            'conditionType' => Join::WITH,
                            'condition' => self::RELATION_NAME . ".entity = '{$className}'"
                                . ' AND ' . self::MARKETING_LIST_ITEM_RELATION_NAME
                                . '.marketingList = ' . self::RELATION_NAME
                        ]
                    ]
                ]
            ]
        ];
    }
}
