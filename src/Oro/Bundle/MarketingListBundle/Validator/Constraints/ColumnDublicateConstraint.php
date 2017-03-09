<?php

namespace Oro\Bundle\MarketingListBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ColumnDublicateConstraint extends Constraint
{
    /**
     * @var string
     */
    public $columnIsDublicate = 'oro.marketinglist.dublicate.columns';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_marketing_list.column_dublicate_validator';
    }
}
