<?php

namespace Oro\Bundle\MarketingListBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This constraint is used to check that a marketing list has a contact information.
 */
class ContactInformationColumn extends Constraint
{
    public string $message = 'oro.marketinglist.contact_information_required';

    public string $typeMessage = 'oro.marketinglist.contact_information_type';

    /** @var string */
    public $field;

    /** @var string */
    public $type;

    #[\Override]
    public function getTargets(): string|array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }

    #[\Override]
    public function getDefaultOption(): ?string
    {
        return 'field';
    }
}
