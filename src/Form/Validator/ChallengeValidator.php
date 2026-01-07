<?php

namespace OpenWebAddict\CaptchetatBundle\Form\Validator;

use OpenWebAddict\CaptchetatBundle\Service\CaptchetatService;

class ChallengeValidator {
    public function __construct(
        protected CaptchetatService $captchetatService
    ){}

    public function validate(string $captchaUuid, string $userEnteredCode): bool
    {
        return $this->captchetatService->validateCaptcha($captchaUuid, $userEnteredCode);
    }
}
