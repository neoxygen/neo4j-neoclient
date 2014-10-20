<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Neoxygen\NeoClient\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Definition implements ConfigurationInterface
{
    protected $allowedModes = array('rest', 'graph', 'row');

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('neoclient');

        $supportedSchemes = array('http', 'https');

        $rootNode->children()
        ->arrayNode('connections')
        ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('scheme')->defaultValue('http')
                        ->validate()
                        ->ifNotInArray($supportedSchemes)
                        ->thenInvalid('The scheme %s is not valid, please choose one of ' . json_encode($supportedSchemes))
                        ->end()
                    ->end()
                    ->scalarNode('host')->defaultValue('localhost')->end()
                    ->integerNode('port')->defaultValue('7474')->end()
                    ->booleanNode('auth')->defaultValue(false)->end()
                    ->scalarNode('user')->end()
                    ->scalarNode('password')->end()
                ->end()
                    ->validate()
                        ->ifTrue(function ($v) {true === $v['auth'] && empty($v['user']) || empty($v['password']);})
                        ->thenInvalid('You must specify a user and a password when using the auth mode')
                    ->end()
            ->end()
        ->end()
        ->arrayNode('extensions')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->canNotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ->arrayNode('loggers')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')->canNotBeEmpty()->end()
                    ->scalarNode('path')->end()
                    ->scalarNode('level')->canNotBeEmpty()->end()
                ->end()
            ->end()
        ->end()
        ->arrayNode('custom_commands')
            ->prototype('array')
                ->children()
                    ->scalarNode('alias')->end()
                    ->scalarNode('class')->end()
                ->end()
            ->end()
        ->end()
        ->arrayNode('fallback')
            ->prototype('scalar')->end()
        ->end()
        ->arrayNode('cache')
            ->canBeUnset()
            ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultValue(false)->end()
                    ->scalarNode('cache_path')->end()
                ->end()
        ->end()
        ->scalarNode('response_format')->defaultValue('json')->end()
        ->scalarNode('response_formatter_class')->defaultNull()->end()
        ->arrayNode('default_result_data_content')
            ->defaultValue(array('row'))
            ->beforeNormalization()
                ->ifString()
                ->then(function ($v) { return array($v); })
                ->end()
            ->prototype('scalar')->end()
            ->validate()
                ->ifTrue(function ($v) {foreach ($v as $k => $m) {if(!in_array($m, $this->allowedModes)) { return true;}}})
                ->thenInvalid('One of the result data contents in "%s" is not valid, please use one of '.json_encode($this->allowedModes))
            ->end()
        ->end()
        ->end();

        return $treeBuilder;
    }
}
