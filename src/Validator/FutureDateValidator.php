<?php

namespace App\Validator;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FutureDateValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FutureDate) {
            throw new UnexpectedTypeException($constraint, FutureDate::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DateTimeInterface) {
            throw new UnexpectedValueException($value, DateTimeInterface::class);
        }

        $now = new DateTimeImmutable();

        if ($value <= $now) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
