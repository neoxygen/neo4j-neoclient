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

namespace Neoxygen\NeoClient;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Neoxygen\NeoClient\DependencyInjection\NeoClientExtension,
    Neoxygen\NeoClient\DependencyInjection\Compiler\ConnectionRegistryCompilerPass,
    Neoxygen\NeoClient\DependencyInjection\Compiler\NeoClientExtensionsCompilerPass,
    Neoxygen\NeoClient\EventListener\HttpRequestEventSubscriber;
use Psr\Log\LoggerInterface,
    Psr\Log\NullLogger,
    Monolog\Logger;

class ServiceContainer
{

    private $serviceContainer;

    private $loadedConfig;

    private $loggers;

    public function __construct(ContainerInterface $serviceContainer = null)
    {
        $this->serviceContainer = null === $serviceContainer ? new ContainerBuilder() : $serviceContainer;

        if (null === $serviceContainer) {
            $this->createDispatcher();
        }

        $this->loggers = array();
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    public function loadConfiguration($filePath)
    {
        $this->loadedConfig = Yaml::parse($filePath);

        return $this;
    }

    public function getLoadedConfig()
    {
        return $this->loadedConfig;
    }

    public function build()
    {
        $extension = new NeoClientExtension();
        $this->serviceContainer->addCompilerPass(new ConnectionRegistryCompilerPass());
        $this->serviceContainer->addCompilerPass(new NeoClientExtensionsCompilerPass());
        $this->serviceContainer->registerExtension($extension);
        $this->serviceContainer->loadFromExtension($extension->getAlias(), $this->loadedConfig['neoclient']);
        $this->serviceContainer->compile();
        if (!empty($this->loadedConfig['neoclient']['loggers'])) {

            $loggerManager = $this->serviceContainer->get('logger');
            foreach ($this->loadedConfig['neoclient']['loggers'] as $name => $config) {
                $loggerManager->createLogger($name, $config);
            }
        }
    }

    public function createDispatcher()
    {
        $definition = new Definition();
        $definition->setClass('Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher')
            ->addArgument($this->serviceContainer);
        $this->serviceContainer->setDefinition('event_dispatcher', $definition);
    }

    public function addSubscribers()
    {
        $this->serviceContainer->get('event_dispatcher')->addSubscriber(new HttpRequestEventSubscriber());
    }

    public function getConnectionManager()
    {
        return $this->serviceContainer->get('neoclient.connection_manager');
    }

    public function getConnection($conn = null)
    {
        return $this->getConnectionManager()->getConnection($conn);
    }

    public function getCommandManager()
    {
        return $this->serviceContainer->get('neoclient.command_manager');
    }

    public function getHttpClient()
    {
        return $this->serviceContainer->get('neoclient.http_client');
    }

    public function invoke($commandAlias, $connectionAlias = null)
    {
        $connection = $this->getConnectionManager()->getConnection($connectionAlias);
        //NeoClient::log('debug', sprintf('Invoking Command "%s" for connection "%s"', $commandAlias, $connection->getAlias()));
        $cmd = $this->getCommandManager()->getCommand($commandAlias);
        $cmd->setConnection($connection);

        return $cmd;
    }

    public function getRoot($conn = null)
    {
        $command = $this->invoke('simple_command', $conn);

        return $command->execute();
    }

    public function ping($conn = null)
    {
        $command = $this->invoke('neo.ping_command', $conn);

        return $command->execute();
    }

    public function getLabels($conn = null)
    {
        $command = $this->invoke('neo.get_labels_command', $conn);

        return $command->execute();
    }

    public function getVersion($conn = null)
    {
        $command = $this->invoke('neo.get_neo4j_version', $conn);

        return $command->execute();
    }

    public function sendCypherQuery($query, array $parameters = array(), $conn = null, array $resultDataContents = array())
    {
        return $this->invoke('neo.send_cypher_query', $conn)
            ->setArguments($query, $parameters, $resultDataContents)
            ->execute();
    }

    public function openTransaction($conn = null)
    {
        return $this->invoke('neo.open_transaction', $conn)
            ->execute();
    }

    public function rollbackTransaction($id, $conn = null)
    {
        return $this->invoke('neo.rollback_transaction', $conn)
            ->setTransactionId($id)
            ->execute();
    }

    public function pushToTransaction($transactionId, $query, array $parameters = array(), $conn = null)
    {
        return $this->invoke('neo.push_to_transaction', $conn)
            ->setArguments($transactionId, $query, $parameters)
            ->execute();
    }

    public function setLogger($name, LoggerInterface $logger)
    {
        return $this->serviceContainer->get('neoclient.logger_manager')->setLogger($name, $logger);
    }

    public function getLogger($name = 'defaultLogger')
    {
        return $this->serviceContainer->get('neoclient.logger_manager')->getLogger($name);
    }

    public function log($level = 'debug', $message, array $context = array())
    {
        return $this->serviceContainer->get('neoclient.logger_manager')->log($level, $message, $context);
    }
}
