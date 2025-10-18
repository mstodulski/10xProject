<?php

namespace App\Tests\Validator;

use App\Validator\FutureDate;
use App\Validator\FutureDateValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FutureDateValidatorTest extends TestCase
{
    private FutureDateValidator $validator;
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->validator = new FutureDateValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testFutureDateIsValid(): void
    {
        $constraint = new FutureDate();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $futureDate = (new DateTimeImmutable())->modify('+1 day');
        $this->validator->validate($futureDate, $constraint);
    }

    public function testPastDateIsInvalid(): void
    {
        $constraint = new FutureDate();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $pastDate = (new DateTimeImmutable())->modify('-1 day');
        $this->validator->validate($pastDate, $constraint);
    }

    public function testCurrentTimeIsInvalid(): void
    {
        $constraint = new FutureDate();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $now = new DateTimeImmutable();
        $this->validator->validate($now, $constraint);
    }

    public function testNullValueIsValid(): void
    {
        $constraint = new FutureDate();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $constraint);
    }
}
