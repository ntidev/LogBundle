<?php

namespace NTI\LogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nti_log');

        $rootNode
            ->children()
                ->arrayNode('database')
                    ->children()
                        ->scalarNode('connection_name')->end()
                    ->end()
                ->end()
                ->arrayNode('exceptions')
                    ->children()
                        ->arrayNode('errors_only')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('exclude')                                    
                    ->prototype('scalar')
                    ->end()                                        
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
