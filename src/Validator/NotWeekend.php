<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NotWeekend extends Constraint
{
    public string $message = 'inspection.start_datetime.not_weekend';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
