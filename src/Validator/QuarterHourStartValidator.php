<?php

namespace App\Validator;

use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class QuarterHourStartValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof QuarterHourStart) {
            throw new UnexpectedTypeException($constraint, QuarterHourStart::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DateTimeInterface) {
            throw new UnexpectedValueException($value, DateTimeInterface::class);
        }

        $minutes = (int) $value->format('i');

        // Valid minutes: 0, 15, 30, 45
        if (!in_array($minutes, [0, 15, 30, 45], true)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
