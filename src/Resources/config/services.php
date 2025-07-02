<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->load('OpenWebAddict\\CaptchetatBundle\\Service\\', '../../Service/*')    
        ->arg('$sandbox', '%captchetat.sandbox%')
        ->arg('$clientId', '%captchetat.client_id%')
        ->arg('$clientSecret', '%captchetat.client_secret%')
        ->autowire()
        ->autoconfigure()
        ->public();
};