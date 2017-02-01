<?php

namespace Oro\Bundle\MarketingActivityBundle\Filter;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use Oro\Bundle\FilterBundle\Filter\DateTimeRangeFilter;

class MarketingActivitiesSectionFilterHelper
{
    /** @var  DateTimeRangeFilter */
    protected $dateTimeRangeFilter;

    /**
     * MarketingActivitiesSectionFilterHelper constructor.
     *
     * @param DateTimeRangeFilter $dateTimeRangeFilter
     */
    public function __construct(DateTimeRangeFilter $dateTimeRangeFilter)
    {
        $this->dateTimeRangeFilter = $dateTimeRangeFilter;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $filterData
     */
    public function addFiltersToQuery(QueryBuilder $queryBuilder, $filterData)
    {
        $dataSourceAdapter = new OrmFilterDatasourceAdapter($queryBuilder);

        if (isset($filterData['startDateRange'])) {
            $this->addDateTimeRangeFilterToQuery(
                $dataSourceAdapter,
                $filterData['startDateRange'],
                'campaign.startDate'
            );
        }

        if (isset($filterData['endDateRange'])) {
            $this->addDateTimeRangeFilterToQuery(
                $dataSourceAdapter,
                $filterData['endDateRange'],
                'campaign.endDate'
            );
        }

        if (!empty($filterData['campaigns']['value'])) {
            $values = array_filter($filterData['campaigns']['value'], function ($value) {
                return !empty($value);
            });
            if (!empty($values)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in('campaign.id', implode(',', $values)));
            }
        }
    }

    /**
     * @param FilterDatasourceAdapterInterface $dataSourceAdapter
     * @param array                            $filterData
     * @param string                           $dataName
     *
     * @return MarketingActivitiesSectionFilterHelper
     */
    public function addDateTimeRangeFilterToQuery(
        FilterDatasourceAdapterInterface $dataSourceAdapter,
        $filterData,
        $dataName
    ) {
        $filter = clone $this->dateTimeRangeFilter;

        $filter->init(
            $dataName,
            ['data_name' => $dataName]
        );
        $datetimeForm = $filter->getForm();
        if (!$datetimeForm->isSubmitted()) {
            $datetimeForm->submit($filterData);
        }
        $filter->apply($dataSourceAdapter, $datetimeForm->getData());

        return $this;
    }
}
