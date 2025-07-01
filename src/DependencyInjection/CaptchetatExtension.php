<?php

namespace OpenWebAddict\CaptchetatBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CaptchetatExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configutation = new CaptchetatConfiguration();
        $config = $this->processConfiguration($configutation, $configs);

        $container->setParameter('captchetat.sandbox', $config['sandbox']);
        $container->setParameter('captchetat.client_id', $config['client_id']);
        $container->setParameter('captchetat.client_secret', $config['client_secret']);

    }
}
