<?php

namespace OpenWebAddict\CaptchetatBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

class CaptchetatExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new CaptchetatConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('captchetat.sandbox', $config['sandbox']);
        $container->setParameter('captchetat.client_id', $config['client_id']);
        $container->setParameter('captchetat.client_secret', $config['client_secret']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

    }
}
