<?php

namespace OpenWebAddict\CaptchetatBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CaptchetatConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('captchetat');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('sandbox')->defaultTrue()->end()
            ->scalarNode('client_id')->isRequired()->end()
            ->scalarNode('client_secret')->isRequired()->end()
            ->end();

        return $treeBuilder;
    }
}
