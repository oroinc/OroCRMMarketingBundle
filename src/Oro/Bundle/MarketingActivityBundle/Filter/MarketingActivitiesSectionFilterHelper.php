<?php

namespace Oro\Bundle\MarketingActivityBundle\Filter;

use Doctrine\ORM\QueryBuilder;

class MarketingActivitiesSectionFilterHelper
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $filterData
     */
    public function addFiltersToQuery(QueryBuilder $queryBuilder, $filterData)
    {
        if (!empty($filterData['campaigns']['value'])) {
            //filter empty values
            $values = array_filter($filterData['campaigns']['value'], function ($value) {
                return !empty($value);
            });
            if (!empty($values)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('campaign.id', ':campaignsIds'))
                    ->setParameter('campaignsIds', $values);
            }
        }
    }
}
