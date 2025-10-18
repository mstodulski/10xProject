<?php

namespace App\Validator;

use App\Entity\Inspection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class WorkingHoursValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof WorkingHours) {
            throw new UnexpectedTypeException($constraint, WorkingHours::class);
        }

        if (!$value instanceof Inspection) {
            throw new UnexpectedValueException($value, Inspection::class);
        }

        $startDatetime = $value->getStartDatetime();
        $endDatetime = $value->getEndDatetime();

        if (null === $startDatetime || null === $endDatetime) {
            return;
        }

        // Check start time
        $startHour = (int) $startDatetime->format('G');
        if ($startHour < $constraint->workingHourStart || $startHour >= $constraint->workingHourEnd) {
            $this->context->buildViolation($constraint->startMessage)
                ->atPath('startDatetime')
                ->setParameter('{{ start }}', (string) $constraint->workingHourStart)
                ->setParameter('{{ end }}', (string) $constraint->workingHourEnd)
                ->addViolation();
        }

        // Check end time - must be <= 16:00:00
        $endHour = (int) $endDatetime->format('G');
        $endMinute = (int) $endDatetime->format('i');
        $endSecond = (int) $endDatetime->format('s');

        $endTimeInSeconds = $endHour * 3600 + $endMinute * 60 + $endSecond;
        $maxEndTimeInSeconds = $constraint->workingHourEnd * 3600;

        if ($endTimeInSeconds > $maxEndTimeInSeconds) {
            $this->context->buildViolation($constraint->endMessage)
                ->atPath('endDatetime')
                ->setParameter('{{ start }}', (string) $constraint->workingHourStart)
                ->setParameter('{{ end }}', (string) $constraint->workingHourEnd)
                ->addViolation();
        }
    }
}
