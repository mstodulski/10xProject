<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class WorkingHours extends Constraint
{
    public string $startMessage = 'inspection.start_datetime.working_hours';
    public string $endMessage = 'inspection.end_datetime.working_hours';
    public int $workingHourStart = 7;
    public int $workingHourEnd = 16;

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
