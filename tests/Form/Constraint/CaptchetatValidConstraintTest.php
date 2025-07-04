<?php

namespace OpenWebAddict\CaptchetatBundle\Tests\Form\Constraint;

use OpenWebAddict\CaptchetatBundle\Form\Constraint\CaptchetatValidConstraint;
use OpenWebAddict\CaptchetatBundle\Form\Validator\CaptchetatChallengeValidator;
use PHPUnit\Framework\TestCase;

class CaptchetatValidConstraintTest extends TestCase
{
    public function testValidateBy(): void
    {
        $constraint = new CaptchetatValidConstraint();
        $this->assertSame(
            CaptchetatChallengeValidator::class,
            $constraint->validatedBy()
        );
    }
}
