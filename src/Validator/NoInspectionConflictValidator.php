<?php

namespace App\Validator;

use App\Entity\Inspection;
use App\Repository\InspectionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NoInspectionConflictValidator extends ConstraintValidator
{
    public function __construct(
        private readonly InspectionRepository $inspectionRepository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoInspectionConflict) {
            throw new UnexpectedTypeException($constraint, NoInspectionConflict::class);
        }

        if (!$value instanceof Inspection) {
            throw new UnexpectedValueException($value, Inspection::class);
        }

        $startDatetime = $value->getStartDatetime();
        $endDatetime = $value->getEndDatetime();

        if (null === $startDatetime || null === $endDatetime) {
            return;
        }

        // Check for conflicts, excluding the current inspection ID if it exists (for updates)
        $hasConflict = $this->inspectionRepository->hasTimeConflict(
            $startDatetime,
            $endDatetime,
            $value->getId()
        );

        if ($hasConflict) {
            $this->context->buildViolation($constraint->message)
                ->atPath('startDatetime')
                ->addViolation();
        }
    }
}
