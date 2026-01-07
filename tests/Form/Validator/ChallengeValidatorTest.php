<?php

namespace OpenWebAddict\CaptchetatBundle\Tests\Form\Validator;

use OpenWebAddict\CaptchetatBundle\Form\Validator\ChallengeValidator;
use OpenWebAddict\CaptchetatBundle\Service\CaptchetatService;
use PHPUnit\Framework\TestCase;

class ChallengeValidatorTest extends TestCase
{
    private CaptchetatService $captchetatService;

    public function setUp(): void 
    {
        $this->captchetatService = $this->createMock(CaptchetatService::class);
    }

    public function testValidateFalse(): void
    {
        $this->captchetatService->expects($this->once())
            ->method('validateCaptcha')
            ->willReturn(false);
        $challengeValidator = new ChallengeValidator($this->captchetatService);
        $this->assertFalse($challengeValidator->validate('uuid', 'answer'));
    }

    public function testValidateTrue(): void
    {
        $this->captchetatService->expects($this->once())
            ->method('validateCaptcha')
            ->willReturn(true);
        $challengeValidator = new ChallengeValidator($this->captchetatService);
        $this->assertTrue($challengeValidator->validate('uuid', 'answer'));
    }
} 
