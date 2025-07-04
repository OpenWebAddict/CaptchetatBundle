<?php

namespace OpenWebAddict\CaptchetatBundle\Tests\Form\Validator;

use OpenWebAddict\CaptchetatBundle\Form\Constraint\CaptchetatValidConstraint;
use OpenWebAddict\CaptchetatBundle\Form\Validator\CaptchetatChallengeValidator;
use OpenWebAddict\CaptchetatBundle\Form\Validator\ChallengeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CaptchetatChallengeValidatorTest extends TestCase
{
    private ChallengeValidator $challengeValidator;

    private CaptchetatChallengeValidator $validator;

    public function setUp(): void
    {
        $this->challengeValidator = $this->createMock(ChallengeValidator::class);

        $this->validator = new CaptchetatChallengeValidator($this->challengeValidator);
    }

    public function testValidateOnContrainstError(): void
    {
        $constraintMock = $this->createMock(Constraint::class);
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate([], $constraintMock);
    }

    public function testValidateOnInvalidValues(): void
    {
        $constraint = new CaptchetatValidConstraint();
        $values = [
            'captchetatAnswer' => null,
            'captchetatUuid' => null,
        ];

        $contraintViolation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $contraintViolation->expects($this->once())->method('addViolation');
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($contraintViolation);
        $this->validator->initialize($context);
        $this->validator->validate($values, $constraint);
    }

    public function testValidateOnChallengeUnvalidate(): void
    {
        $constraint = new CaptchetatValidConstraint();
        $values = [
            'captchetatAnswer' => 'answer',
            'captchetatUuid' => 'uuid',
        ];

        $this->challengeValidator->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        $contraintViolation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $contraintViolation->expects($this->once())->method('addViolation');
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->once())
            ->method('buildViolation')
            ->willReturn($contraintViolation);
        $this->validator->initialize($context);
        $this->validator->validate($values, $constraint);
    }


    public function testValidate(): void
    {
        $constraint = new CaptchetatValidConstraint();
        $values = [
            'captchetatAnswer' => 'answer',
            'captchetatUuid' => 'uuid',
        ];

        $this->challengeValidator->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $contraintViolation = $this->createMock(ConstraintViolationBuilderInterface::class);
        $contraintViolation->expects($this->never())->method('addViolation');
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->never())
            ->method('buildViolation')
            ->willReturn($contraintViolation);
        $this->validator->initialize($context);
        $this->validator->validate($values, $constraint);
    }
}
