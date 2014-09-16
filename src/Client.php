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

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Neoxygen\NeoClient\DependencyInjection\NeoClientExtension,
    Neoxygen\NeoClient\DependencyInjection\Compiler\ConnectionRegistryCompilerPass,
    Neoxygen\NeoClient\DependencyInjection\Compiler\NeoClientExtensionsCompilerPass;

class Client
{
    /**
     * @var ContainerBuilder
     */
    private $serviceContainer;

    /**
     * @var array
     */
    private $configuration = array();

    /**
     * @var bool Whether or not a config file has already been loaded
     */
    private $configLoaded = false;

    /**
     * @var array
     */
    private $listeners = array();

    /**
     * @var array
     */
    private $loggers = array();

    /**
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(ContainerInterface $serviceContainer = null)
    {
        if (null === $serviceContainer) {
            $this->serviceContainer = new ContainerBuilder();
        }

        return $this;
    }

    /**
     * @return array The current configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string  $alias  An alias for the connection
     * @param string  $scheme The scheme of the connection
     * @param string  $host   The host of the connection
     * @param integer $port   The port for the connection
     *
     * @return Neoxygen\NeoClient\Client
     */
    public function addConnection($alias, $scheme, $host, $port)
    {
        $this->configuration['connections'][$alias] = array(
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port
        );

        return $this;
    }

    /**
     * @param $event
     * @param $listener
     * @return $this
     */
    public function addEventListener($event, $listener)
    {
        $this->listeners[] = array($event, $listener);

        return $this;
    }

    /**
     * @return array
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * @param $name
     * @param LoggerInterface $logger
     */
    public function setLogger($name, LoggerInterface $logger)
    {
        if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = $logger;
        }
    }

    /**
     * @return array
     */
    public function getLoggers()
    {
        return $this->loggers;
    }

    /**
     *
     */
    public function build()
    {
        $extension = new NeoClientExtension();
        $this->serviceContainer->registerExtension($extension);
        $this->serviceContainer->addCompilerPass(new ConnectionRegistryCompilerPass());
        $this->serviceContainer->addCompilerPass(new NeoClientExtensionsCompilerPass());
        $this->serviceContainer->loadFromExtension($extension->getAlias(), $this->configuration);
        $this->serviceContainer->compile();

        foreach ($this->listeners as $event => $callback) {
            $this->serviceContainer->get('event_dispatcher')->addListener($event, $callback);
        }
    }

    /**
     * @return ContainerBuilder
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return object
     */
    public function getConnectionManager()
    {
        return $this->serviceContainer->get('neoclient.connection_manager');
    }

    /**
     * @param  null  $alias
     * @return mixed
     */
    public function getConnection($alias = null)
    {
        return $this->getConnectionManager()->getConnection($alias);
    }
}
