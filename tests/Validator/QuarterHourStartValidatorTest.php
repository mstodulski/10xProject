<?php

namespace App\Tests\Validator;

use App\Validator\QuarterHourStart;
use App\Validator\QuarterHourStartValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class QuarterHourStartValidatorTest extends TestCase
{
    private QuarterHourStartValidator $validator;
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->validator = new QuarterHourStartValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testValidQuarterHours(): void
    {
        $constraint = new QuarterHourStart();

        $this->context->expects($this->never())
            ->method('buildViolation');

        // Test all valid quarter hours
        $validTimes = ['10:00', '10:15', '10:30', '10:45'];
        foreach ($validTimes as $time) {
            $datetime = new DateTimeImmutable("2025-10-20 {$time}:00");
            $this->validator->validate($datetime, $constraint);
        }
    }

    public function testInvalidMinutes(): void
    {
        $constraint = new QuarterHourStart();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $datetime = new DateTimeImmutable('2025-10-20 10:05:00');
        $this->validator->validate($datetime, $constraint);
    }

    public function testNullValueIsValid(): void
    {
        $constraint = new QuarterHourStart();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $constraint);
    }
}
