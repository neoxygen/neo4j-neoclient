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
    Symfony\Component\Config\FileLocator;
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
        $this->registerCustomCommands($config);

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
            if (isset($settings['auth']) && true === $settings['auth']) {
                $definition->addArgument(true)
                    ->addArgument($settings['user'])
                    ->addArgument($settings['password']);
            }
            $container->setDefinition(sprintf('neoclient.connection.%s', $connectionAlias), $definition);
        }
    }

    private function addRegisteredExtensionsDefinitions($config, $container)
    {
        foreach ($config['extensions'] as $alias => $props) {
            $this->registerCoreExtension($alias, $props);
        }

        // Registering Core Commands
        $this->registerCoreExtension('neoclient_core', array('class' => 'Neoxygen\NeoClient\Extension\NeoClientCoreExtension'));
        $this->registerCoreExtension('neoclient_auth', array('class' => 'Neoxygen\NeoClient\Extension\NeoClientAuthExtension'));
    }

    private function registerCoreExtension($alias, $props)
    {
        $definition = new Definition();
        $definition->setClass($props['class'])
            ->addTag('neoclient.extension_class');
        $this->container->setDefinition(sprintf('neoclient.extension_%s', $alias), $definition);
    }

    private function registerCustomCommands(array $config)
    {
        foreach ($config['custom_commands'] as $command) {
            $definition = new Definition();
            $definition->setClass($command['class']);
            $definition->addTag('neoclient.custom_command', array($command['alias']));
            $this->container->setDefinition(sprintf('neoclient.custom_command.%s', $command['alias']), $definition);
        }
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
