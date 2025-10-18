<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class MaxTwoWeeksAhead extends Constraint
{
    public string $message = 'inspection.start_datetime.max_two_weeks';
    public int $maxWeeks = 2;

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
