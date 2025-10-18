<?php

namespace App\Tests\Validator;

use App\Entity\Inspection;
use App\Entity\User;
use App\Repository\InspectionRepository;
use App\Validator\NoInspectionConflict;
use App\Validator\NoInspectionConflictValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NoInspectionConflictValidatorTest extends TestCase
{
    private NoInspectionConflictValidator $validator;
    private ExecutionContextInterface $context;
    private InspectionRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(InspectionRepository::class);
        $this->validator = new NoInspectionConflictValidator($this->repository);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    private function createInspection(?int $id = null): Inspection
    {
        $inspection = new Inspection();
        $user = new User();

        if ($id !== null) {
            // Use reflection to set the ID for testing purposes
            $reflection = new \ReflectionClass($inspection);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($inspection, $id);
        }

        $inspection->setStartDatetime(new DateTimeImmutable('2025-10-20 10:00:00'))
            ->setEndDatetime(new DateTimeImmutable('2025-10-20 10:30:00'))
            ->setVehicleMake('Toyota')
            ->setVehicleModel('Corolla')
            ->setLicensePlate('ABC123')
            ->setClientName('Jan Kowalski')
            ->setPhoneNumber('123456789')
            ->setCreatedByUser($user);

        return $inspection;
    }

    public function testNoConflictIsValid(): void
    {
        $constraint = new NoInspectionConflict();
        $inspection = $this->createInspection();

        $this->repository->expects($this->once())
            ->method('hasTimeConflict')
            ->with(
                $this->equalTo($inspection->getStartDatetime()),
                $this->equalTo($inspection->getEndDatetime()),
                null
            )
            ->willReturn(false);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($inspection, $constraint);
    }

    public function testConflictIsInvalid(): void
    {
        $constraint = new NoInspectionConflict();
        $inspection = $this->createInspection();

        $this->repository->expects($this->once())
            ->method('hasTimeConflict')
            ->with(
                $this->equalTo($inspection->getStartDatetime()),
                $this->equalTo($inspection->getEndDatetime()),
                null
            )
            ->willReturn(true);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('atPath')
            ->with('startDatetime')
            ->willReturnSelf();
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $this->validator->validate($inspection, $constraint);
    }

    public function testExcludesCurrentInspectionWhenUpdating(): void
    {
        $constraint = new NoInspectionConflict();
        $inspection = $this->createInspection(123); // Inspection with ID 123

        $this->repository->expects($this->once())
            ->method('hasTimeConflict')
            ->with(
                $this->equalTo($inspection->getStartDatetime()),
                $this->equalTo($inspection->getEndDatetime()),
                123 // Should exclude this ID
            )
            ->willReturn(false);

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($inspection, $constraint);
    }

}
