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

use Monolog\Logger;
use Neoxygen\NeoClient\Exception\CommandException,
    Neoxygen\NeoClient\Transaction\Transaction;
use Psr\Log\NullLogger,
    Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Dumper\PhpDumper,
    Symfony\Component\Yaml\Yaml;
use Neoxygen\NeoClient\DependencyInjection\NeoClientExtension,
    Neoxygen\NeoClient\DependencyInjection\Compiler\ConnectionRegistryCompilerPass,
    Neoxygen\NeoClient\DependencyInjection\Compiler\NeoClientExtensionsCompilerPass;

class ClientBuilder
{
    const CACHE_FILENAME = 'neoclient_container.php';

    /**
     * @var ContainerBuilder
     */
    private $serviceContainer;

    /**
     * @var array Configuration array
     */
    private $configuration = array();

    /**
     * @var array the Configuration loaded with a config file
     */
    private $loadedConfig;

    /**
     * @var array The collection of registered listeners
     */
    private $listeners = array();

    /**
     * @var array The collection of the registered loggers
     */
    private $loggers = array();

    public static function create()
    {
        return new static();
    }

    /**
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(ContainerInterface $serviceContainer = null)
    {
        if (null === $serviceContainer) {
            $this->serviceContainer = new ContainerBuilder();
        }
        $this->configuration['cache']['enabled'] = false;

        return $this;
    }

    /**
     * @return array The current configuration
     */
    public function getConfiguration()
    {
        if (null !== $this->loadedConfig) {
            $conf = array_merge($this->configuration, $this->loadedConfig);

            return $conf;
        }

        return $this->configuration;
    }

    public function loadConfigurationFile($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('Configuration file "%s" not found', $file));
        }
        $this->loadedConfig = Yaml::parse($file);

        return $this;
    }

    /**
     * @param string  $alias    An alias for the connection
     * @param string  $scheme   The scheme of the connection
     * @param string  $host     The host of the connection
     * @param integer $port     The port for the connection
     * @param bool    $authMode Whether or not the connection use the authentication extension
     * @param string|null Authentication login
     * @param string|null Authentication password
     *
     * @return Neoxygen\NeoClient\Client
     */
    public function addConnection($alias, $scheme, $host, $port, $authMode = false, $authUser = null, $authPassword = null)
    {
        $this->configuration['connections'][$alias] = array(
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'auth' => $authMode,
            'user' => $authUser,
            'password' => $authPassword
        );

        return $this;
    }

    /**
     * Defines a fallback connection for a given connection
     *
     * @param  string $connectionAlias
     * @param  string $fallbackConnectionAlias
     * @return $this
     */
    public function setFallbackConnection($connectionAlias, $fallbackConnectionAlias)
    {
        $this->configuration['fallback'][$connectionAlias] = $fallbackConnectionAlias;

        return $this;
    }

    /**
     * Sets the default result data content for the http transactional cypher
     *
     * @param  array $defaultResultDataContent array containing values of "graph", "rest" or "row"
     * @return $this
     */
    public function setDefaultResultDataContent(array $defaultResultDataContent = array('row'))
    {
        $this->configuration['default_result_data_content'] = $defaultResultDataContent;

        return $this;
    }

    /**
     * Adds an event listener to an event
     *
     * @param  string          $event    The Event to listen to
     * @param  string|\Closure $listener The listener, can be a Closure, a callback function or a class
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
     * Register a user defined logger
     *
     * @param string          $name   Logger channel name
     * @param LoggerInterface $logger User logger instance
     */
    public function setLogger($name, LoggerInterface $logger)
    {
        if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = $logger;
        }

        return $this;
    }

    /**
     * @return array[$name => LoggerInterface] The registered loggers
     */
    public function getLoggers()
    {
        if (empty($this->loggers)) {
            $this->createNullLogger();
        }

        return $this->loggers;
    }

    /**
     * Returns a registered Logger
     *
     * @param  string|null     $name The name of the Logger
     * @return LoggerInterface The logger bounded to the specified name
     */
    public function getLogger($name = null)
    {
        if (null === $name && !isset($this->loggers['nullLogger'])) {
            $this->createNullLogger();
            $name = 'nullLogger';
        }

        return $this->loggers[$name];
    }

    /**
     * Logs a record to the registered loggers
     *
     * @param string $level   Record logging level
     * @param string $message Log record message
     * @param array  $context Context of message
     */
    public function log($level = 'debug', $message, array $context = array())
    {
        foreach ($this->getLoggers() as $key => $logger) {
            $logger->log($level, $message, $context);
        }
    }

    /**
     * Creates an internal stream logger
     *
     * @param  string     $name  Logger channel name
     * @param  string     $path  Path to the log file
     * @param  int|string $level Logging level
     * @return $this
     */
    public function createDefaultStreamLogger($name, $path, $level = Logger::DEBUG)
    {
        $logger = new Logger($name);
        $handler = new \Monolog\Handler\StreamHandler($path, $level);
        $logger->pushHandler($handler);
        $this->loggers[$name] = $logger;

        return $this;
    }

    /**
     * Creates an internal chrome php logger
     *
     * @param  string     $name  Logger channel name
     * @param  int|string $level Logging level
     * @return $this
     */
    public function createDefaultChromePHPLogger($name, $level = Logger::DEBUG)
    {
        $logger = new Logger($name);
        $handler = new \Monolog\Handler\ChromePHPHandler($level);
        $logger->pushHandler($handler);
        $this->loggers[$name] = $logger;

        return $this;
    }

    private function createNullLogger()
    {
        $logger = new NullLogger();
        $this->loggers['nullLogger'] = $logger;
    }

    /**
     * Register a user custom command
     *
     * @param  string $alias Command alias
     * @param  string $class The Command class name
     * @return $this
     */
    public function registerCommand($alias, $class)
    {
        $this->configuration['custom_commands'][] = array(
            'alias' => $alias,
            'class' => $class
        );

        return $this;
    }

    /**
     * Register a user custom extension
     *
     * @param  string $alias The extension alias
     * @param  string $class The class name of the extension
     * @return $this
     */
    public function registerExtension($alias, $class)
    {
        $this->configuration['extensions'][$alias] = array('class' => $class);

        return $this;
    }

    /**
     * Enables the cache option for the container dumping
     *
     * @param  string $cachePath The cache path
     * @return $this
     */
    public function enableCache($cachePath)
    {
        $this->configuration['cache']['enabled'] = true;
        $this->configuration['cache']['cache_path'] = $cachePath;

        return $this;
    }

    /**
     * @return bool True if the cache is enabled, false otherwise
     */
    public function isCacheEnabled()
    {
        $cf = $this->getConfiguration();
        if ($cf['cache']['enabled'] === true) {
            return true;
        }

        return false;
    }

    /**
     * @return null|string The defined cache path, null if cache disabled
     */
    public function getCachePath()
    {
        if (!$this->isCacheEnabled()) {
            return null;
        }

        $path = $this->getConfiguration()['cache']['cache_path'];
        if (!preg_match('#/$#', $path)) {
            $path = $path . '/';
        }

        return $path;
    }

    /**
     * Builds the service definitions and processes the configuration
     */
    public function build()
    {
        if ($this->isCacheEnabled()) {
            $file = $this->getCachePath() . self::CACHE_FILENAME;
            if (file_exists($file)) {
                require_once $file;
                $this->serviceContainer = new \ProjectServiceContainer();

                return true;
            }
        }
        //$this->serviceContainer->setParameter('neoclient.response_format', 'array');
        $extension = new NeoClientExtension();
        $this->serviceContainer->registerExtension($extension);
        $this->serviceContainer->addCompilerPass(new ConnectionRegistryCompilerPass());
        $this->serviceContainer->addCompilerPass(new NeoClientExtensionsCompilerPass());
        $this->serviceContainer->loadFromExtension($extension->getAlias(), $this->getConfiguration());
        $this->serviceContainer->compile();

        foreach ($this->listeners as $event => $callback) {
            $this->serviceContainer->get('event_dispatcher')->addListener($event, $callback);
        }

        foreach ($this->loggers as $alias => $logger) {
            $this->serviceContainer->get('logger')->setLogger($alias, $logger);
        }

        if ($this->isCacheEnabled()) {
            $dumper = new PhpDumper($this->serviceContainer);
            file_put_contents($file, $dumper->dump());
        }

        $client = $this->serviceContainer->get('neoclient.client');

        return $client;
    }

    /**
     * @return ContainerBuilder
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Returns the ConnectionManager Service
     *
     * @return Neoxygen\NeoClient\Connection\ConnectionManager
     */
    public function getConnectionManager()
    {
        return $this->serviceContainer->get('neoclient.connection_manager');
    }

    /**
     * Returns the connection bound to the alias, or the default connection if no alias is provided
     *
     * @param  string|null                              $alias
     * @return Neoxygen\NeoClient\Connection\Connection The connection with alias "$alias"
     */
    public function getConnection($alias = null)
    {
        return $this->getConnectionManager()->getConnection($alias);
    }

    /**
     * Returns the CommandManager Service
     *
     * @return Neoxygen\NeoClient\Command\CommandManager
     */
    public function getCommandManager()
    {
        return $this->serviceContainer->get('neoclient.command_manager');
    }

    /**
     * @return bool Whether or not the DIC has been compiled
     */
    public function isFrozen()
    {
        return true === $this->getServiceContainer()->isFrozen();
    }

}
