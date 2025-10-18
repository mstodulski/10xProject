<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NoInspectionConflict extends Constraint
{
    public string $message = 'inspection.conflict.exists';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
