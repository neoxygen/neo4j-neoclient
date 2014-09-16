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

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\DependencyInjection\Extension\ExtensionInterface,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Neoxygen\NeoClient\DependencyInjection\Definition as ConfigDefinition;

class NeoClientExtension implements  ExtensionInterface
{
    protected $container;

    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $processor = new Processor();
        $configuration = new ConfigDefinition();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

        $this->addConnectionDefinitions($config, $container);
        $this->addRegisteredExtensionsDefinitions($config, $container);
        $this->addListeners($config);

    }

    private function addConnectionDefinitions($config, $container)
    {
        foreach ($config['connections'] as $connectionAlias => $settings) {
            if ($container->hasDefinition(sprintf('neoclient.connection.%s', $connectionAlias))) {
                throw new \InvalidArgumentException(sprintf('The connection %s can only be declared once, check your config file', $connectionAlias));
            }

            $definition = new Definition();
            $definition
                ->setClass('Neoxygen\NeoClient\Connection\Connection')
                ->addArgument($connectionAlias)
                ->addArgument($settings['scheme'])
                ->addArgument($settings['host'])
                ->addArgument($settings['port'])
                ->addTag('neoclient.registered_connection')
                ->setLazy(true);
            $container->setDefinition(sprintf('neoclient.connection.%s', $connectionAlias), $definition);
        }
    }

    private function addRegisteredExtensionsDefinitions($config, $container)
    {
        foreach ($config['extensions'] as $alias => $props) {
            $this->registerCommandExtension($alias, $props);
        }

        // Registering Core Commands
        $this->registerCommandExtension('neoclient_core', array('class' => 'Neoxygen\NeoClient\Extension\NeoClientCoreExtension'));
    }

    private function registerCommandExtension($alias, $props)
    {
        $definition = new Definition();
        $definition->setClass($props['class'])
            ->addTag('neoclient.extension_class');
        $this->container->setDefinition(sprintf('neoclient.extension_', $alias), $definition);
    }

    private function addListeners(array $config)
    {

    }

    public function getAlias()
    {
        return 'neoclient';
    }

    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getNamespace()
    {
        return false;
    }
}
