<?php

namespace OpenWebAddict\CaptchetatBundle\Controller;

use OpenWebAddict\CaptchetatBundle\Service\CaptchetatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CaptchetaController extends AbstractController {
    public function __construct(
        private CaptchetatService $service
    )
    {}

    public function apiSimpleCaptchaEndpointAction() {
        
    }
}
