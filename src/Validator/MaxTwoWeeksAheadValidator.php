<?php

namespace App\Validator;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class MaxTwoWeeksAheadValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxTwoWeeksAhead) {
            throw new UnexpectedTypeException($constraint, MaxTwoWeeksAhead::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DateTimeInterface) {
            throw new UnexpectedValueException($value, DateTimeInterface::class);
        }

        $now = new DateTimeImmutable();
        $maxDate = $now->add(new DateInterval(sprintf('P%dW', $constraint->maxWeeks)));

        if ($value > $maxDate) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ weeks }}', (string) $constraint->maxWeeks)
                ->addViolation();
        }
    }
}
