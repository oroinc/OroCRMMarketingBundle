<?php

namespace Oro\Bundle\MarketingListBundle\Validator\Constraints;

use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates that a marketing list has a contact information.
 */
class ContactInformationColumnValidator extends ConstraintValidator
{
    private ContactInformationFieldHelper $contactInformationFieldHelper;

    public function __construct(ContactInformationFieldHelper $contactInformationFieldHelper)
    {
        $this->contactInformationFieldHelper = $contactInformationFieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ContactInformationColumn) {
            throw new UnexpectedTypeException($constraint, ContactInformationColumn::class);
        }

        if ($constraint->field && !is_string($constraint->field)) {
            throw new UnexpectedTypeException($constraint->field, 'string');
        }

        if (!empty($constraint->field)) {
            $propertyAccess = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccess->getValue($value, $constraint->field);
        }

        if (!$value instanceof AbstractQueryDesigner) {
            throw new UnexpectedTypeException($value, AbstractQueryDesigner::class);
        }

        $type = $constraint->type;
        if (!$this->assertContactInformationFields($value, $type)) {
            $parameters = [];
            if ($constraint->type) {
                $message = $constraint->typeMessage;
                $parameters['%type%'] = $constraint->type;
            } else {
                $message = $constraint->message;
            }

            if ($constraint->field) {
                $this->context->buildViolation($message)
                    ->atPath($constraint->field)
                    ->setParameters($parameters)
                    ->addViolation();
            } else {
                $this->context->addViolation($message, $parameters);
            }
        }
    }

    /**
     * Assert that value has contact information column in it's definition.
     */
    private function assertContactInformationFields(AbstractQueryDesigner $value, ?string $type): bool
    {
        $contactInformationFields = $this->contactInformationFieldHelper->getQueryContactInformationFields($value);
        if ($type) {
            return array_key_exists($type, $contactInformationFields) && count($contactInformationFields[$type]);
        }

        return (bool)count($contactInformationFields);
    }
}
