<?php

namespace OpenWebAddict\CaptchetatBundle\Form\Validator;

use OpenWebAddict\CaptchetatBundle\Form\Constraint\CaptchetatValidConstraint;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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

        $captchaAnswer = $value['captchetatAnswer'];
        $captchaUuid = $value['captchetatUuid'];

        if (null === $captchaAnswer || null === $captchaUuid) {
            $this->context
                ->buildViolation("Aucune valeur saisie dans le captcha")
                ->addViolation();
        } elseif (!$this->challengeValidator->validate($captchaUuid, $captchaAnswer)) {
            $this->context
                ->buildViolation("Le résultat du captcha est invalide")
                ->addViolation();
        }
    }
}
