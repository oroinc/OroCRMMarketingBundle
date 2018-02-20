<?php

namespace Oro\Bundle\MarketingActivityBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

abstract class AbstractTypeCountFunction implements FunctionInterface
{
    /**
     * Returns type id
     *
     * @return string
     */
    abstract protected function getType();

    /**
     * {@inheritdoc}
     */
    public function getExpression($tableAlias, $fieldName, $columnName, $columnAlias, AbstractQueryConverter $qc)
    {
        list($alias, $name) = explode('.', $columnName);
        if ($name === 'type') {
            // Make sure type table joined when marketing activity is used as virtual relation
            // @Todo: remove after BAP-13387 is fixed
            $alias = $qc->ensureChildTableJoined($alias, 'type', 'inner');
        }

        return sprintf(
            "SUM(CASE WHEN %s.id = '%s' THEN 1 ELSE 0 END)",
            $alias,
            $this->getType()
        );
    }
}
