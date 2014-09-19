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
use PhpSpec\ServiceContainer;
use Psr\Log\NullLogger,
    Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Dumper\PhpDumper,
    Symfony\Component\Yaml\Yaml;
use Neoxygen\NeoClient\DependencyInjection\NeoClientExtension,
    Neoxygen\NeoClient\DependencyInjection\Compiler\ConnectionRegistryCompilerPass,
    Neoxygen\NeoClient\DependencyInjection\Compiler\NeoClientExtensionsCompilerPass;

class Client
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
     * @param $event The Event to listen to
     * @param $listener The listener, can be a Closure, a callback function or a class
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
     * @param  null            $name The name of the Logger
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

    public function log($level = 'debug', $message, array $context = array())
    {
        foreach ($this->getLoggers() as $key => $logger) {
            $logger->log($level, $message, $context);
        }
    }

    public function createDefaultStreamLogger($name, $path, $level = Logger::DEBUG)
    {
        $logger = new Logger($name);
        $handler = new \Monolog\Handler\StreamHandler($path, $level);
        $logger->pushHandler($handler);
        $this->loggers[$name] = $logger;

        return $this;
    }

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

    public function registerCommand($alias, $class)
    {
        $this->configuration['custom_commands'][] = array(
            'alias' => $alias,
            'class' => $class
        );

        return $this;
    }

    public function registerExtension($alias, $class)
    {
        $this->configuration['extensions'][$alias] = array('class' => $class);

        return $this;
    }

    /**
     * Enables the cache option for the container dumping
     *
     * @param $cachePath
     * @return $this
     */
    public function enableCache($cachePath)
    {
        $this->configuration['cache']['enabled'] = true;
        $this->configuration['cache']['cache_path'] = $cachePath;

        return $this;
    }

    public function isCacheEnabled()
    {
        $cf = $this->getConfiguration();
        if ($cf['cache']['enabled'] === true) {
            return true;
        }

        return false;
    }

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

    /**
     * Invokes a Command by alias and connectionAlias(optional)
     *
     * @param $commandAlias The alias of the Command to invoke
     * @param  string|null                                 $connectionAlias
     * @return Neoxygen\NeoClient\Command\CommandInterface
     */
    public function invoke($commandAlias, $connectionAlias = null)
    {
        $command = $this->getCommandManager()->getCommand($commandAlias);
        $command->setConnection($connectionAlias);

        return $command;
    }

    /**
     * Convenience method that returns the root of the Neo4j Api
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function getRoot($conn = null)
    {
        $command = $this->invoke('simple_command', $conn);

        return $command->execute();
    }

    /**
     * Convenience method for pinging the Connection
     *
     * @param  string|null $conn The alias of the connection to use
     * @return null        The command treating the ping will throw an Exception if the connection can not be made
     */
    public function ping($conn = null)
    {
        $command = $this->invoke('neo.ping_command', $conn);

        return $command->execute();
    }

    /**
     * Convenience method that invoke the GetLabelsCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function getLabels($conn = null)
    {
        $command = $this->invoke('neo.get_labels_command', $conn);

        return $command->execute();
    }

    /**
     * Convenience method that invoke the GetVersionCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function getVersion($conn = null)
    {
        $command = $this->invoke('neo.get_neo4j_version', $conn);

        return $command->execute();
    }

    /**
     * Convenience method that invoke the sendCypherQueryCommand
     * and passes given query and parameters arguments
     *
     * @param  string      $query              The query to send
     * @param  array       $parameters         Map of query parameters
     * @param  string|null $conn               The alias of the connection to use
     * @param  array       $resultDataContents
     * @return mixed
     */
    public function sendCypherQuery($query, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        return $this->invoke('neo.send_cypher_query', $conn)
            ->setArguments($query, $parameters, $resultDataContents)
            ->execute();
    }

    /**
     * Convenience method that invoke the OpenTransactionCommand
     *
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function openTransaction($conn = null)
    {
        return $this->invoke('neo.open_transaction', $conn)
            ->execute();
    }

    /**
     * Convenience method that invoke the RollBackTransactionCommand
     *
     * @param $id The id of the transaction
     * @param  string|null $conn The alias of the connection to use
     * @return mixed
     */
    public function rollBackTransaction($id, $conn = null)
    {
        return $this->invoke('neo.rollback_transaction', $conn)
            ->setTransactionId($id)
            ->execute();
    }

    /**
     * Convenience method that invoke the PushToTransactionCommand
     * and passes the query and parameters as arguments
     *
     * @param $transactionId The transaction id
     * @param $query The query to send
     * @param  array       $parameters Parameters map of the query
     * @param  string|null $conn       The alias of the connection to use
     * @return mixed
     */
    public function pushToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        return $this->invoke('neo.push_to_transaction', $conn)
            ->setArguments($transactionId, $query, $parameters)
            ->execute();
    }

    /**
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function listUsers($connectionAlias = null)
    {
        return $this->invoke('neo.list_users', $connectionAlias)
            ->execute();
    }

    /**
     * @param $user
     * @param $password
     * @param  bool        $readOnly
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function addUser($user, $password, $readOnly = false, $connectionAlias = null)
    {
        return $this->invoke('neo.add_user', $connectionAlias)
            ->setReadOnly($readOnly)
            ->setUser($user)
            ->setPassword($password)
            ->execute();
    }

    /**
     * @param $user
     * @param $password
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function removeUser($user, $password, $connectionAlias = null)
    {
        return $this->invoke('neo.remove_user', $connectionAlias)
            ->setUser($user)
            ->setPassword($password)
            ->execute();
    }

    /**
     * @param  string|null $uuid
     * @param  int|null    $limit
     * @param  int|null    $moduleId
     * @param  string|null $connectionAlias
     * @return mixed
     */
    public function getChangeFeed($uuid = null, $limit = null, $moduleId = null, $connectionAlias = null)
    {
        return $this->invoke('neo.changefeed', $connectionAlias)
            ->setUuid($uuid)
            ->setLimit($limit)
            ->setModuleId($moduleId)
            ->execute();
    }

    /**
     * Convenience method for working with replication
     * Sends a read only query
     *
     * @param  string      $query
     * @param  array       $parameters
     * @param  string|null $connectionAlias
     * @param  array       $resultDataContents
     * @return mixed
     */
    public function sendReadQuery($query, array $parameters = array(), $connectionAlias = null, array $resultDataContents = array())
    {
        foreach (array('MERGE', 'CREATE') as $pattern) {
            if (preg_match('/'.$pattern.'/i', $query)) {
                throw new \InvalidArgumentException(sprintf('The query "%s" contains cypher write clauses', $query));
            }
        }

        return $this->sendCypherQuery($query, $parameters, $connectionAlias, $resultDataContents, false);
    }

    /**
     * Convenience method for working with replication
     *
     * @param  string      $query
     * @param  array       $parameters
     * @param  string|null $connectionAlias
     * @param  array       $resultDataContents
     * @return mixed
     */
    public function sendWriteQuery($query, array $parameters = array(), $connectionAlias = null, array $resultDataContents = array())
    {
        return $this->sendCypherQuery($query, $parameters, $connectionAlias, $resultDataContents, true);
    }

    /**
     * Convenience method for the replication mode
     * Push a read only query to the transaction
     *
     * @param $transactionId
     * @param $query
     * @param  array $parameters
     * @param  null  $conn
     * @param  array $resultDataContents
     * @return mixed
     */
    public function pushReadQueryToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array())
    {
        foreach (array('MERGE', 'CREATE') as $pattern) {
            if (preg_match('/'.$pattern.'/i', $query)) {
                throw new \InvalidArgumentException(sprintf('The query "%s" contains cypher write clauses', $query));
            }
        }

        return $this->pushToTransaction($transactionId, $query, $parameters, $conn, $resultDataContents, false);
    }

    /**
     * Convenience method for working with the replication mode
     *
     * @param $transactionId
     * @param $query
     * @param  array $parameters
     * @param  null  $conn
     * @param  array $resultDataContents
     * @return mixed
     */
    public function pushWriteQueryToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array())
    {
        return $this->pushToTransaction($transactionId, $query, $parameters, $conn, $resultDataContents, true);
    }
}
