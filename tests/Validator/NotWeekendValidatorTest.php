<?php

namespace App\Tests\Validator;

use App\Validator\NotWeekend;
use App\Validator\NotWeekendValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NotWeekendValidatorTest extends TestCase
{
    private NotWeekendValidator $validator;
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->validator = new NotWeekendValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testWeekdaysAreValid(): void
    {
        $constraint = new NotWeekend();

        $this->context->expects($this->never())
            ->method('buildViolation');

        // Monday to Friday
        $weekdays = ['2025-10-20', '2025-10-21', '2025-10-22', '2025-10-23', '2025-10-24'];
        foreach ($weekdays as $date) {
            $datetime = new DateTimeImmutable("{$date} 10:00:00");
            $this->validator->validate($datetime, $constraint);
        }
    }

    public function testSaturdayIsInvalid(): void
    {
        $constraint = new NotWeekend();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        // Saturday
        $datetime = new DateTimeImmutable('2025-10-25 10:00:00');
        $this->validator->validate($datetime, $constraint);
    }

    public function testSundayIsInvalid(): void
    {
        $constraint = new NotWeekend();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        // Sunday
        $datetime = new DateTimeImmutable('2025-10-26 10:00:00');
        $this->validator->validate($datetime, $constraint);
    }

    public function testNullValueIsValid(): void
    {
        $constraint = new NotWeekend();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $constraint);
    }
}
