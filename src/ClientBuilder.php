<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient;

use Monolog\Logger;
use Neoxygen\NeoClient\Exception\Neo4jException;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\Yaml\Yaml;
use Neoxygen\NeoClient\DependencyInjection\NeoClientExtension;
use Neoxygen\NeoClient\DependencyInjection\Compiler\ConnectionRegistryCompilerPass;
use Neoxygen\NeoClient\DependencyInjection\Compiler\NeoClientExtensionsCompilerPass;
use Neoxygen\NeoClient\DependencyInjection\Compiler\AliasesCompilerPass;
use Neoxygen\NeoClient\DependencyInjection\Compiler\EventSubscribersCompilerPass;

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

    /**
     * @var bool if the check for a Ha Failure file have to be skipped or not
     */
    private $skipHaFailureFileCheck = false;

    /**
     */
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

    /**
     * Load a configuration from an external YAML file.
     *
     * @param string $file
     *
     * @return ClientBuilder
     */
    public function loadConfigurationFile($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('Configuration file "%s" not found', $file));
        }
        $this->loadedConfig = Yaml::parse(file_get_contents($file));

        return $this;
    }

    /**
     * Load a user defined configuration array
     *
     * @param array $configuration
     *
     * @return ClientBuilder
     */
    public function loadConfiguration(array $configuration)
    {
        $this->loadedConfig = $configuration;

        return $this;
    }

    /**
     * @param string $alias    An alias for the connection
     * @param string $scheme   The scheme of the connection
     * @param string $host     The host of the connection
     * @param int    $port     The port for the connection
     * @param bool   $authMode Whether or not the connection use the authentication extension
     * @param string|null Authentication login
     * @param string|null Authentication password
     *
     * @return ClientBuilder
     */
    public function addConnection($alias, $scheme, $host, $port, $authMode = false, $authUser = null, $authPassword = null)
    {
        $this->configuration['connections'][$alias] = array(
            'scheme' => $scheme,
            'host' => $host,
            'port' => $port,
            'auth' => $authMode,
            'user' => $authUser,
            'password' => $authPassword,
        );

        return $this;
    }

    /**
     * Add a default local connection running at http://localhost:7474.
     *
     * @return ClientBuilder
     */
    public function addDefaultLocalConnection()
    {
        return $this->addConnection('default', 'http', 'localhost', 7474);
    }

    public function setMasterConnection($connectionAlias)
    {
        $this->checkConnection($connectionAlias);

        $this->configuration['ha_mode']['master'] = $connectionAlias;

        return $this;
    }

    public function setSlaveConnection($connectionAlias)
    {
        $this->checkConnection($connectionAlias);

        $this->configuration['ha_mode']['slaves'][] = $connectionAlias;

        return $this;
    }

    /**
     * Enables the High Availibility Mode.
     *
     * @return $this
     */
    public function enableHAMode()
    {
        $this->configuration['ha_mode']['enabled'] = true;
        $this->configuration['ha_mode']['type'] = 'enterprise';

        return $this;
    }

    public function configureHAQueryModeHeaders($headerKey, $writeModeHeaderValue, $readModeHeaderValue)
    {
        $this->configuration['ha_mode']['query_mode_header_key'] = $headerKey;
        $this->configuration['ha_mode']['write_mode_header_value'] = $writeModeHeaderValue;
        $this->configuration['ha_mode']['read_mode_header_value'] = $readModeHeaderValue;
    }

    /**
     * Defines a fallback connection for a given connection.
     *
     * @param string $connectionAlias
     * @param string $fallbackConnectionAlias
     *
     * @return $this
     */
    public function setFallbackConnection($connectionAlias, $fallbackConnectionAlias)
    {
        $this->configuration['fallback'][$connectionAlias] = $fallbackConnectionAlias;

        return $this;
    }

    /**
     * Sets the default timeout for the http connection request.
     *
     * @param int $seconds
     *
     * @return $this
     */
    public function setDefaultTimeout($seconds)
    {
        $this->configuration['default_timeout'] = (int) $seconds;

        return $this;
    }

    /**
     * Sets whether or not the response from the API should be formatted by the ResponseFormatter.
     *
     * @param bool $auto
     *
     * @return $this
     */
    public function setAutoFormatResponse($auto = false)
    {
        $this->configuration['auto_format_response'] = $auto;

        return $this;
    }

    /**
     * Defines the class to use as Response Formatter, should implements <code>\Neoxygen\NeoClient\Formatter\ResponseFormatterInterface</code>.
     *
     * @param string $class
     */
    public function setResponseFormatterClass($class)
    {
        if (null === $class) {
            throw new \InvalidArgumentException('A string should be passed as response formatter class');
        }

        $this->configuration['response_formatter_class'] = $class;

        return $this;
    }

    /**
     * Enables the new formatting service from GraphAware.
     */
    public function enableNewFormattingService()
    {
        $this->configuration['enable_new_response_format_mode'] = true;

        return $this;
    }

    /**
     * Adds an event listener to an event.
     *
     * @param string          $event    The Event to listen to
     * @param string|\Closure $listener The listener, can be a Closure, a callback function or a class
     *
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
     * Register a user defined logger.
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
     * Returns a registered Logger.
     *
     * @param string|null $name The name of the Logger
     *
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
     * Logs a record to the registered loggers.
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
     * Creates an internal stream logger.
     *
     * @param string     $name  Logger channel name
     * @param string     $path  Path to the log file
     * @param int|string $level Logging level
     *
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
     * Creates an internal chrome php logger.
     *
     * @param string     $name  Logger channel name
     * @param int|string $level Logging level
     *
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
     * Register a user custom command.
     *
     * @param string $alias Command alias
     * @param string $class The Command class name
     *
     * @return $this
     */
    public function registerCommand($alias, $class)
    {
        $this->configuration['custom_commands'][] = array(
            'alias' => $alias,
            'class' => $class,
        );

        return $this;
    }

    /**
     * Register a user custom extension.
     *
     * @param string $alias The extension alias
     * @param string $class The class name of the extension
     *
     * @return $this
     */
    public function registerExtension($alias, $class)
    {
        $this->configuration['extensions'][$alias] = array('class' => $class);

        return $this;
    }

    /**
     * Enables the cache option for the container dumping.
     *
     * @param string $cachePath The cache path
     *
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
            return;
        }

        $path = $this->getConfiguration()['cache']['cache_path'];
        if (!preg_match('#/$#', $path)) {
            $path = $path.'/';
        }

        return $path;
    }

    /**
     * Builds the service definitions and processes the configuration.
     *
     * @return \Neoxygen\NeoClient\Client
     */
    public function build()
    {
        if ($this->isCacheEnabled()) {
            $file = $this->getCachePath().self::CACHE_FILENAME;
            if (file_exists($file)) {
                include_once $file;
                $this->serviceContainer = new \ProjectServiceContainer();

                return new Client($this->serviceContainer);
            }
        }
        $extension = new NeoClientExtension();
        $this->serviceContainer->registerExtension($extension);
        $this->serviceContainer->addCompilerPass(new AliasesCompilerPass());
        $this->serviceContainer->addCompilerPass(new ConnectionRegistryCompilerPass());
        $this->serviceContainer->addCompilerPass(new NeoClientExtensionsCompilerPass());
        $this->serviceContainer->addCompilerPass(new EventSubscribersCompilerPass());
        $this->serviceContainer->loadFromExtension($extension->getAlias(), $this->getConfiguration());
        $this->serviceContainer->compile();
        if ($this->serviceContainer->hasParameter('loggers')) {
            foreach ($this->serviceContainer->getParameter('loggers') as $channel => $logger) {
                switch ($logger['type']) {
                    case 'stream':
                        $this->createDefaultStreamLogger($channel, $logger['path'], $logger['level']);
                }
            }
        }

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

        if (!$this->skipHaFailureFileCheck) {
            $this->checkForHaConfig();
        }

        $client = $this->getClient();

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
     * @return bool Whether or not the DIC has been compiled
     */
    public function isFrozen()
    {
        return true === $this->getServiceContainer()->isFrozen();
    }

    /**
     * Erases the HAFailureFile.
     *
     * @return $this
     */
    public function resetHaFailureFile()
    {
        $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'neoclient_ha_config_after_failure';
        if (file_exists($file)) {
            unlink($file);
        }

        return $this;
    }

    /**
     * Returns whether or not the check for the failure file should be done.
     *
     * @return $this
     */
    public function skipHaFailureFileCheck()
    {
        $this->skipHaFailureFileCheck = true;

        return $this;
    }

    private function checkConnection($alias)
    {
        if (!array_key_exists($alias, $this->configuration['connections'])) {
            throw new Neo4jException(sprintf('The connection "%s" has not been registered', '%s'));
        }

        return true;
    }

    private function checkForHaConfig()
    {
        if ($this->serviceContainer->has('neoclient.ha_manager')) {
            $file = sys_get_temp_dir().DIRECTORY_SEPARATOR.'neoclient_ha_config_after_failure';
            if (!file_exists($file)) {
                return;
            }
            $content = file_get_contents($file);
            $config = Yaml::parse($content);
            $cm = $this->serviceContainer->get('neoclient.connection_manager');
            $all = $cm->getConnectionAliases();
            if (isset($config['new_master'])) {
                $newConfig['master'] = $config['new_master'];
                unset($all[$config['new_master']]);
            } else {
                $newConfig['master'] = $cm->getMasterConnectionAlias();
                unset($all[$cm->getMasterConnectionAlias()]);
            }
            if (isset($config['primary_slave'])) {
                $newConfig['slaves'][] = $config['primary_slave'];
                unset($all[$config['primary_slave']]);
            }
            foreach ($all as $slave) {
                $newConfig['slaves'][] = $slave;
            }

            $cm->setMasterConnection($newConfig['master']);
            $cm->setSlaveConnections($newConfig['slaves']);
        }
    }

    /**
     * @return \Neoxygen\NeoClient\Client
     */
    private function getClient()
    {
        return $this->serviceContainer->get('neoclient.client');
    }
}
