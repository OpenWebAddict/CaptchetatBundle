<?php

namespace OpenWebAddict\CaptchetatBundle\Form\Constraint;

use OpenWebAddict\CaptchetatBundle\Form\Validator\CaptchetatChallengeValidator;
use Symfony\Component\Validator\Constraint;

class CaptchetatValidConstraint extends Constraint 
{
    public function validatedBy(): string
    {
        return CaptchetatChallengeValidator::class;
    }
}
