<?php

namespace App\Tests\Validator;

use App\Entity\Inspection;
use App\Entity\User;
use App\Validator\WorkingHours;
use App\Validator\WorkingHoursValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class WorkingHoursValidatorTest extends TestCase
{
    private WorkingHoursValidator $validator;
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->validator = new WorkingHoursValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    private function createInspection(string $startTime, string $endTime): Inspection
    {
        $inspection = new Inspection();
        $user = new User();

        $inspection->setStartDatetime(new DateTimeImmutable("2025-10-20 {$startTime}"))
            ->setEndDatetime(new DateTimeImmutable("2025-10-20 {$endTime}"))
            ->setVehicleMake('Toyota')
            ->setVehicleModel('Corolla')
            ->setLicensePlate('ABC123')
            ->setClientName('Jan Kowalski')
            ->setPhoneNumber('123456789')
            ->setCreatedByUser($user);

        return $inspection;
    }

    public function testValidWorkingHours(): void
    {
        $constraint = new WorkingHours();

        $this->context->expects($this->never())
            ->method('buildViolation');

        // Test various valid working hour combinations
        $validCombinations = [
            ['07:00:00', '07:30:00'],
            ['10:00:00', '10:30:00'],
            ['15:30:00', '16:00:00'],
        ];

        foreach ($validCombinations as [$start, $end]) {
            $inspection = $this->createInspection($start, $end);
            $this->validator->validate($inspection, $constraint);
        }
    }

    public function testStartBeforeWorkingHoursIsInvalid(): void
    {
        $constraint = new WorkingHours();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('startDatetime')
            ->willReturnSelf();
        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->startMessage)
            ->willReturn($violationBuilder);

        $inspection = $this->createInspection('06:30:00', '07:00:00');
        $this->validator->validate($inspection, $constraint);
    }

    public function testStartAtOrAfterWorkingHoursEndIsInvalid(): void
    {
        $constraint = new WorkingHours();

        // This will trigger both start and end violations since 16:00 is out of range
        // and 16:30 is also out of range
        $violationBuilderStart = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilderStart->expects($this->once())
            ->method('atPath')
            ->with('startDatetime')
            ->willReturnSelf();
        $violationBuilderStart->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilderStart->expects($this->once())
            ->method('addViolation');

        $violationBuilderEnd = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilderEnd->expects($this->once())
            ->method('atPath')
            ->with('endDatetime')
            ->willReturnSelf();
        $violationBuilderEnd->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilderEnd->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->exactly(2))
            ->method('buildViolation')
            ->willReturnCallback(function ($message) use ($constraint, $violationBuilderStart, $violationBuilderEnd) {
                if ($message === $constraint->startMessage) {
                    return $violationBuilderStart;
                }
                return $violationBuilderEnd;
            });

        $inspection = $this->createInspection('16:00:00', '16:30:00');
        $this->validator->validate($inspection, $constraint);
    }

    public function testEndAfterWorkingHoursIsInvalid(): void
    {
        $constraint = new WorkingHours();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('endDatetime')
            ->willReturnSelf();
        $violationBuilder->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->endMessage)
            ->willReturn($violationBuilder);

        $inspection = $this->createInspection('15:45:00', '16:15:00');
        $this->validator->validate($inspection, $constraint);
    }

    public function testEndExactlyAtWorkingHoursEndIsValid(): void
    {
        $constraint = new WorkingHours();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $inspection = $this->createInspection('15:30:00', '16:00:00');
        $this->validator->validate($inspection, $constraint);
    }
}
