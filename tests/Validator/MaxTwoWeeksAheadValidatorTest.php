<?php

namespace App\Tests\Validator;

use App\Validator\MaxTwoWeeksAhead;
use App\Validator\MaxTwoWeeksAheadValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MaxTwoWeeksAheadValidatorTest extends TestCase
{
    private MaxTwoWeeksAheadValidator $validator;
    private ExecutionContextInterface $context;

    protected function setUp(): void
    {
        $this->validator = new MaxTwoWeeksAheadValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($this->context);
    }

    public function testDateWithinTwoWeeksIsValid(): void
    {
        $constraint = new MaxTwoWeeksAhead();

        $this->context->expects($this->never())
            ->method('buildViolation');

        // Test various dates within 2 weeks
        $dates = [
            (new DateTimeImmutable())->modify('+1 day'),
            (new DateTimeImmutable())->modify('+7 days'),
            (new DateTimeImmutable())->modify('+13 days'),
            (new DateTimeImmutable())->modify('+14 days'),
        ];

        foreach ($dates as $date) {
            $this->validator->validate($date, $constraint);
        }
    }

    public function testDateBeyondTwoWeeksIsInvalid(): void
    {
        $constraint = new MaxTwoWeeksAhead();

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder->expects($this->once())
            ->method('setParameter')
            ->with('{{ weeks }}', '2')
            ->willReturnSelf();
        $violationBuilder->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $farFuture = (new DateTimeImmutable())->modify('+15 days');
        $this->validator->validate($farFuture, $constraint);
    }

    public function testNullValueIsValid(): void
    {
        $constraint = new MaxTwoWeeksAhead();

        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $constraint);
    }
}
