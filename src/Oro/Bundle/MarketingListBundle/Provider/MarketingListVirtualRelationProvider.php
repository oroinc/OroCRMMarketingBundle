<?php

namespace Oro\Bundle\MarketingListBundle\Provider;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\VirtualRelationProviderInterface;

class MarketingListVirtualRelationProvider implements VirtualRelationProviderInterface
{
    const RELATION_NAME = 'marketingList_virtual';
    const MARKETING_LIST_ITEM_RELATION_NAME = 'marketingListItems';
    const MARKETING_LIST_BY_ENTITY_CACHE_KEY = 'oro_marketing_list.lists_by_entities';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param CacheProvider  $cacheProvider
     */
    public function __construct(DoctrineHelper $doctrineHelper, CacheProvider $cacheProvider)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isVirtualRelation($className, $fieldName)
    {
        return $this->hasMarketingList($className) && $fieldName === self::RELATION_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelationQuery($className, $fieldName)
    {
        $relations = $this->getVirtualRelations($className);
        if (array_key_exists($fieldName, $relations)) {
            return $relations[$fieldName]['query'];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getVirtualRelations($className)
    {
        if ($this->hasMarketingList($className)) {
            return [self::RELATION_NAME => $this->getRelationDefinition($className)];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetJoinAlias($className, $fieldName, $selectFieldName = null)
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

    /**
     * @param string $className
     * @return bool
     */
    public function hasMarketingList($className)
    {
        if (!$this->cacheProvider->contains(static::MARKETING_LIST_BY_ENTITY_CACHE_KEY)) {
            $marketingListByEntity = [];

            $repository = $this->doctrineHelper->getEntityRepository('OroMarketingListBundle:MarketingList');
            $qb = $repository->createQueryBuilder('ml')
                ->select('ml.entity')
                ->distinct(true);
            $entities = $qb->getQuery()->getArrayResult();

            foreach ($entities as $entity) {
                $marketingListByEntity[$entity['entity']] = true;
            }

            $this->cacheProvider->save(static::MARKETING_LIST_BY_ENTITY_CACHE_KEY, $marketingListByEntity);

            return !empty($marketingListByEntity[$className]);
        }

        $marketingListByEntity = $this->cacheProvider->fetch(static::MARKETING_LIST_BY_ENTITY_CACHE_KEY);

        return !empty($marketingListByEntity[$className]);
    }

    /**
     * @param string $className
     * @return array
     */
    public function getRelationDefinition($className)
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
