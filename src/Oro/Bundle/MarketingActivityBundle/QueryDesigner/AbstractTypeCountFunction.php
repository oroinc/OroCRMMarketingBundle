<?php

namespace Oro\Bundle\MarketingActivityBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;

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
        // split by dot $columnName
        // there we will have tableAlias.columnName for dictionary virtual column
        list($statusTableAlias) = explode('.', $columnName);

        return sprintf(
            "SUM(CASE WHEN %s.id = '%s' THEN 1 ELSE 0 END)",
            $statusTableAlias,
            $this->getType()
        );
    }
}
