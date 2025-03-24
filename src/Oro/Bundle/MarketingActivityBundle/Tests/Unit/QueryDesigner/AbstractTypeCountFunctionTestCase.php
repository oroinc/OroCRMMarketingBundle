<?php

namespace Oro\Bundle\MarketingActivityBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\MarketingActivityBundle\QueryDesigner\AbstractTypeCountFunction;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\AbstractQueryConverter;
use PHPUnit\Framework\TestCase;

abstract class AbstractTypeCountFunctionTestCase extends TestCase
{
    protected AbstractTypeCountFunction $function;
    protected string $type;

    public function testGetExpression(): void
    {
        $expression = $this->function->getExpression(
            'alias',
            'fieldName',
            'type_enum.id',
            'columnAlias',
            $this->createMock(AbstractQueryConverter::class)
        );
        self::assertEquals(
            sprintf("SUM(CASE WHEN type_enum.id = '%s' THEN 1 ELSE 0 END)", $this->type),
            $expression
        );
    }
}
