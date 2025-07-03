<?php

namespace OpenWebAddict\CaptchetatBundle\Form\Validator;

use OpenWebAddict\CaptchetatBundle\Form\Constraint\CaptchetatValidConstraint;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class CaptchetatChallengeValidator extends ConstraintValidator
{
    public function __construct(
        private ChallengeValidator $challengeValidator
    )
    {}

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof CaptchetatValidConstraint) {
            throw new UnexpectedTypeException($constraint, CaptchetatValidConstraint::class);
        }

        if (!isset($value['captchetatAnswer']) || !isset($value['captchetatUuid'])) {
            throw new UnexpectedValueException($value, 'array');
        }

        $captchaAnswer = $value['captchetatAnswer'];
        $captchaUuid = $value['captchetatUuid'];

        if (null === $captchaAnswer || null === $captchaUuid) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        } elseif (!$this->challengeValidator->validate($captchaUuid, $captchaAnswer)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
