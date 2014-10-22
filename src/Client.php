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
     * @param  string                                      $commandAlias    The alias of the Command to invoke
     * @param  string|null                                 $connectionAlias
     * @return Neoxygen\NeoClient\Command\CommandInterface
     */
    public function invoke($commandAlias, $connectionAlias = null)
    {
        if (!$this->isFrozen()) {
            throw new \RuntimeException('The commands can not be used while the application has not been built.
            You maybe forgot to add the "->build" chained method to the client construction?');

        }
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
     * Returns the registered constraints
     *
     * @param  string|null $conn
     * @return mixed
     */
    public function getConstraints($conn = null)
    {
        $command = $this->invoke('neo.get_constraints_command', $conn);

        return $command->execute();
    }

    /**
     * Returns the list of indexed properties for a given Label
     *
     * @param  string      $label
     * @param  string|null $conn
     * @return mixed
     */
    public function listIndex($label, $conn = null)
    {
        $command = $this->invoke('neo.list_index_command', $conn);
        $command->setArguments($label);

        return $command->execute();
    }

    /**
     * Checks if a property is indexed for a given label
     *
     * @param  string      $label
     * @param  string      $propertyKey
     * @param  string|null $conn
     * @return bool
     */
    public function isIndexed($label, $propertyKey, $conn = null)
    {
        $indexes = json_decode($this->listIndex($label, $conn), true);
        foreach ($indexes as $index) {
            if (in_array($propertyKey, $index['property_keys'])) {
                return true;
            }
        }

        return false;
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
        $command = $this->invoke('neo.send_cypher_query', $conn);
        $response_format = $this->getServiceContainer()->getParameter('neoclient.response_format');
        if ('custom' === $response_format) {
            $formatter = $this->getServiceContainer()->get('neoclient.response_formatter');
            $requiredRDC = $formatter::getDefaultResultDataContents();
        }
        $rdc = !(empty($resultDataContents)) ? $resultDataContents : $this->getServiceContainer()->getParameter('neoclient.default_result_data_content');
        if (isset($requiredRDC)) {
            $rdc = array_merge($rdc, $requiredRDC);
        }

        return $command->setArguments($query, $parameters, $rdc)
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
     * @param  int         $id   The id of the transaction
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
     * @param  int         $transactionId The transaction id
     * @param  string      $query         The query to send
     * @param  array       $parameters    Parameters map of the query
     * @param  string|null $conn          The alias of the connection to use
     * @return mixed
     */
    public function pushToTransaction($transactionId, $query, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        return $this->invoke('neo.push_to_transaction', $conn)
            ->setArguments($transactionId, $query, $parameters)
            ->execute();
    }

    public function pushMultipleToTransaction($transactionId, array $statements, $conn = null, array $resultDataContents = array())
    {
        return $this->invoke('neo.push_multiple_to_transaction', $conn)
            ->setArguments($transactionId, $statements, $resultDataContents)
            ->execute();
    }

    /**
     * Convenience method that commit the transaction
     * and passes the optional query and parameters as arguments
     *
     * @param  int         $transactionId The transaction id
     * @param  string|null $query         The query to send
     * @param  array       $parameters    Parameters map of the query
     * @param  string|null $conn          The alias of the connection to use
     * @return mixed
     */
    public function commitTransaction($transactionId, $query = null, array $parameters = array(), $conn = null, array $resultDataContents = array(), $writeMode = true)
    {
        return $this->invoke('neo.commit_transaction', $conn)
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
     * @param  string      $user
     * @param  string      $password
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
     * @param  string      $user
     * @param  string      $password
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

    /**
     * Creates an index on a label
     *
     * @param  string $label
     * @param  string $property
     * @return bool
     */
    public function createIndex($label, $property)
    {
        $query = 'CREATE INDEX ON :'.$label.'('.$property.')';
        $response = $this->sendCypherQuery($query);
        if (!empty($response['errors'])) {
            throw new CommandException($response['errors'][0]['message']);
        }

        return true;
    }

    /**
     * Drops an index on a label
     *
     * @param  string $label
     * @param  string $property
     * @return bool
     */
    public function dropIndex($label, $property)
    {
        $query = 'DROP INDEX ON :'.$label.'('.$property.')';
        $response = $this->sendCypherQuery($query);
        if (!empty($response['errors'])) {
            throw new CommandException($response['errors'][0]['message']);
        }

        return true;
    }

    /**
     * Create a unique constraint on a label
     *
     * @param  string $label
     * @param  string $property
     * @return bool
     */
    public function createConstraint($label, $property)
    {
        $identifier = strtolower($label);
        $query = 'CREATE CONSTRAINT ON ('.$identifier.':'.$label.') ASSERT '.$identifier.'.'.$property.' IS UNIQUE';
        $response = $this->sendCypherQuery($query);

        if (!empty($response['errors'])) {
            throw new CommandException($response['errors'][0]['message']);
        }

        return true;
    }

    /**
     * Drops a unique constraint on a label
     *
     * @param  string $label
     * @param  string $property
     * @return bool
     */
    public function dropConstraint($label, $property)
    {
        $identifier = strtolower($label);
        $query = 'DROP CONSTRAINT ON ('.$identifier.':'.$label.') ASSERT '.$identifier.'.'.$property.' IS UNIQUE';
        $response = $this->sendCypherQuery($query);

        if (!empty($response['errors'])) {
            throw new CommandException($response['errors'][0]['message']);
        }

        return true;
    }

    /**
     * Creates a new Transaction Handler
     *
     * @param  string|null $conn The connection alias
     * @return Transaction
     */
    public function createTransaction($conn = null)
    {
        $transaction = new Transaction($conn, $this);

        return $transaction;
    }

    /**
     * Retrieve paths between two nodes
     *
     * @param  array       $startNodeProperties
     * @param  array       $endNodeProperties
     * @param  int|null    $depth
     * @param  string|null $direction
     * @param  string|null $conn
     * @return mixed
     */
    public function getPathBetween(array $startNodeProperties, array $endNodeProperties, $depth = null, $direction = 'ALL', $conn = null)
    {
        $this->checkPathNode($startNodeProperties);
        $this->checkPathNode($endNodeProperties);
        $parameters = [];
        $startNPattern = '(start';
        if (isset($startNodeProperties['label']) && !empty($startNodeProperties['label'])) {
            if (is_array($startNodeProperties['label'])) {
                $label = implode(':', $startNodeProperties['label']);
            } else {
                $label = ':'.$startNodeProperties['label'];
            }
            $startNPattern .= $label;
        }
        if (isset($startNodeProperties['properties']) && !empty($startNodeProperties['properties'])) {
            $startNPattern .= ' {';
            $propsCount = count($startNodeProperties['properties']);
            $i = 0;
            foreach ($startNodeProperties['properties'] as $key => $value) {
                $startNPattern .= $key . ': {start_'.$key.'}';
                if ($value instanceof \DateTime) {
                    $value = $value->format('Ymdhis');
                }
                $parameters['start_'.$key] = $value;
                if ($i < $propsCount -1) {
                    $startNPattern .= ', ';
                }
                $i++;
            }
            $startNPattern .= '}';
        }
        $startNPattern .= ')';

        $endNPattern = '(end';
        if (isset($endNodeProperties['label']) && !empty($endNodeProperties['label'])) {
            if (is_array($endNodeProperties['label'])) {
                $label = implode(':', $endNodeProperties['label']);
            } else {
                $label = ':'.$endNodeProperties['label'];
            }
            $endNPattern .= $label;
        }
        if (isset($endNodeProperties['properties']) && !empty($endNodeProperties['properties'])) {
            $endNPattern .= ' {';
            $propsCount = count($endNodeProperties['properties']);
            $i = 0;
            foreach ($endNodeProperties['properties'] as $key => $value) {
                $endNPattern .= $key . ': {end_'.$key.'}';
                if ($value instanceof \DateTime) {
                    $value = $value->format('Ymdhis');
                }
                $parameters['end_'.$key] = $value;
                if ($i < $propsCount -1) {
                    $endNPattern .= ', ';
                }
                $i++;
            }
            $endNPattern .= '}';
        }
        $endNPattern .= ')';
        if (null === $depth) {
            $rel = '[*]';
        } else {
            $d = (int) $depth;
            $rel = '[*1..'.$d.']';
        }
        switch ($direction) {
            case 'ALL':
                $in = '-';
                $out = '-';
                break;
            case 'IN':
                $in = '<-';
                $out = '-';
                break;
            case 'OUT':
                $in = '-';
                $out = '->';
                break;
            default:
                throw new \InvalidArgumentException('The direction must be IN, OUT or ALL');
        }
        $q = 'MATCH p='.$startNPattern.$in.$rel.$out.$endNPattern.' RETURN p';

        return $this->sendCypherQuery($q, $parameters, $conn, array('graph', 'row'));
    }

    private function checkPathNode(array $node)
    {
        if ( (!isset($node['label']) || empty($node['label'])) && (!isset($node['properties']) || empty($node['properties']))) {
            throw new \InvalidArgumentException('The node must contain a label or properties');
        }
    }

}
