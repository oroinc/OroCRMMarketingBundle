<?php

namespace Oro\Bundle\MarketingActivityBundle\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\FunctionInterface;

/**
 * Abstract class for TypeCountFunctions
 */
abstract class AbstractTypeCountFunction implements FunctionInterface
{
    /**
     * Returns type id
     */
    abstract protected function getType(): string;

    #[\Override]
    public function getExpression($tableAlias, $fieldName, $columnName, $columnAlias, AbstractQueryConverter $qc)
    {
        list($alias, $name) = explode('.', $columnName);
        if ($name === 'type') {
            // Make sure type table joined when marketing activity is used as a virtual relation -
            // problem STR without this workaround is described in BAP-13387.
            $alias = $qc->ensureChildTableJoined($alias, 'type', 'inner');
        }

        return sprintf(
            "SUM(CASE WHEN %s.id = '%s' THEN 1 ELSE 0 END)",
            $alias,
            $this->getType()
        );
    }
}
