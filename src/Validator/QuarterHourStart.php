<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class QuarterHourStart extends Constraint
{
    public string $message = 'inspection.start_datetime.quarter_hour';

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
