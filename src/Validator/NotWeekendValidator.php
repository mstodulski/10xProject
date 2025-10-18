<?php

namespace App\Validator;

use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NotWeekendValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotWeekend) {
            throw new UnexpectedTypeException($constraint, NotWeekend::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DateTimeInterface) {
            throw new UnexpectedValueException($value, DateTimeInterface::class);
        }

        // Saturday = 6, Sunday = 0 (or 7 depending on format)
        $dayOfWeek = (int) $value->format('N'); // 1 (Monday) through 7 (Sunday)

        if (in_array($dayOfWeek, [6, 7], true)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
