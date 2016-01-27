<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                        ->thenInvalid('The scheme %s is not valid, please choose one of '.json_encode($supportedSchemes))
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
        ->integerNode('default_timeout')->defaultValue(5)->end()
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
                    ->scalarNode('channel')->canNotBeEmpty()->end()
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
        ->arrayNode('ha_mode')
            ->children()
                ->booleanNode('enabled')->defaultValue(false)->end()
                ->scalarNode('query_mode_header_key')->defaultValue('Neo4j-Query-Mode')->end()
                ->scalarNode('write_mode_header_value')->defaultValue('NEO4J_QUERY_WRITE')->end()
                ->scalarNode('read_mode_header_value')->defaultValue('NEO4J_QUERY_READ')->end()
                ->scalarNode('type')->canNotBeEmpty()->end()
                ->scalarNode('master')->end()
                ->arrayNode('slaves')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ->arrayNode('cache')
            ->canBeUnset()
            ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultValue(false)->end()
                    ->scalarNode('cache_path')->end()
                ->end()
        ->end()
        ->scalarNode('response_formatter_class')->defaultValue('Neoxygen\NeoClient\Formatter\ResponseFormatter')->end()
        ->booleanNode('auto_format_response')->defaultValue(false)->end()
        ->booleanNode('enable_new_response_format_mode')->defaultValue(false)->end()
        ->end()
        ->end();

        return $treeBuilder;
    }
}
