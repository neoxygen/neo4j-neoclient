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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
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

        if ($config['cache']['enabled'] === true) {
            $container->setParameter('neoclient.cache_path', $config['cache']['cache_path']);
        }

        $this->addConnectionDefinitions($config, $container);
        $this->addRegisteredExtensionsDefinitions($config, $container);
        $this->addListeners($config);
        $this->registerCustomCommands($config);
        $container->setParameter('loggers', $config['loggers']);
        $container->setParameter('default_timeout', $config['default_timeout']);

        $formatterClass = $config['response_formatter_class'];
        $container->setParameter('response_formatter_class', $formatterClass);
        $resultDataContent = $formatterClass::getDefaultResultDataContents();
        $container->setParameter('neoclient.response_format', $resultDataContent);
        $container->setParameter('neoclient.auto_format_response', $config['auto_format_response']);
        $container->setParameter('neoclient.result_data_content', $resultDataContent);
        $container->setParameter('neoclient.new_format_mode_enabled', $config['enable_new_response_format_mode']);

        if (isset($config['ha_mode'])) {
            $connectionManager = $container->getDefinition('neoclient.connection_manager');
            $commandManager = $container->getDefinition('neoclient.command_manager');
            $httpClient = $container->getDefinition('neoclient.http_client');
            $type = $config['ha_mode']['type'];
            switch ($type) {
                case 'enterprise':
                    $definition = new Definition();
                    $definition
                        ->setClass('Neoxygen\NeoClient\HighAvailibility\HAEnterpriseManager')
                        ->addArgument($connectionManager)
                        ->addArgument($commandManager)
                        ->addArgument($httpClient)
                        ->addArgument($config['ha_mode']['query_mode_header_key'])
                        ->addArgument($config['ha_mode']['write_mode_header_value'])
                        ->addArgument($config['ha_mode']['read_mode_header_value'])
                        ->addTag('neoclient.service_event_subscriber');
                    $container->setDefinition('neoclient.ha_manager', $definition);
                    break;
                case 'community':
                    $definition = new Definition();
                    $definition
                        ->setClass('Neoxygen\NeoClient\HighAvailibility\HACommunityManager')
                        ->addArgument($connectionManager)
                        ->addArgument($httpClient)
                        ->addTag('neoclient.service_event_subscriber');
                    $container->setDefinition('neoclient.ha_manager', $definition);
                    break;
            }
        }
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
            if ($fallbackOf = $this->isFallbackConnection($config, $connectionAlias)) {
                $definition->addTag('neoclient.fallback_connection', array('fallback_of' => $fallbackOf, 'connection_alias' => $connectionAlias));
            }
            if (isset($config['ha_mode']['master']) && $config['ha_mode']['master'] == $connectionAlias) {
                $definition->addTag('neoclient.ha_master');
            }
            if (isset($config['ha_mode']['slaves']) && in_array($connectionAlias, $config['ha_mode']['slaves'])) {
                $definition->addTag('neoclient.ha_slave');
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

    private function isFallbackConnection(array $config, $alias)
    {
        if (isset($config['fallback'])) {
            foreach ($config['fallback'] as $con => $fallback) {
                if ($alias === $fallback) {
                    return $con;
                }
            }
        }

        return false;
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
