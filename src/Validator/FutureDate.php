<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class FutureDate extends Constraint
{
    public string $message = 'inspection.start_datetime.future';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
