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

use Neoxygen\NeoClient\Transaction\PreparedTransaction;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @method getRoot($conn = null)
 * @method ping($conn = null)
 * @method getLabels($conn = null)
 * @method \Neoxygen\NeoClient\Schema\UniqueConstraint createSchemaUniqueConstraint($label, $property, $conn = null)
 * @method getConstraints($conn = null)
 * @method listIndex($label, $conn = null)
 * @method listIndexes(array $labels = array(), $conn = null)
 * @method isIndexed($label, $propertyKey, $conn = null)
 * @method getVersion($conn = null)
 * @method openTransaction($conn = null)
 * @method \Neoxygen\NeoClient\Transaction\Transaction createTransaction($conn = null)
 * @method rollbackTransaction($id, $conn = null)
 * @method \Neoxygen\NeoClient\Formatter\Response sendCypherQuery($query, array $parameters = array(), $conn = null)
 * @method sendMultiple(array $statements, $conn = null)
 * @method sendWriteQuery($query, array $parameters = array())
 * @method sendReadQuery($query, array $parameters = array())
 * @method PreparedTransaction prepareTransaction($conn = null)
 * @method bool createIndex($label, $property)
 * @method bool createUniqueConstraint($label, $property, $removeIndexIfExist = false)
 */
class Client
{
    const NEOCLIENT_VERSION = '3.2.0';

    const NEOCLIENT_QUERY_MODE_WRITE = 'WRITE';

    const NEOCLIENT_QUERY_MODE_READ = 'READ';

    private static $serviceContainer;

    public static $logger;

    private $lastResponse;

    public function __construct(ContainerInterface $container)
    {
        self::$serviceContainer = $container;
        self::$logger = $container->get('logger');
    }

    public static function getNeoClientVersion()
    {
        return self::NEOCLIENT_VERSION;
    }

    public static function commitPreparedTransaction(PreparedTransaction $transaction)
    {
        return self::call('sendMultiple', array($transaction->getStatements(), $transaction->getConnection(), $transaction->getQueryMode()));
    }

    /**
     * @param $method
     * @param $attributes
     *
     * @return \Neoxygen\NeoClient\Request\Response
     */
    private static function call($method, $attributes)
    {
        $extManager = self::$serviceContainer->get('neoclient.extension_manager');

        $response = $extManager->execute($method, $attributes);

        return $response;
    }

    /**
     * Returns the ConnectionManager Service.
     *
     * @return \Neoxygen\NeoClient\Connection\ConnectionManager
     */
    public function getConnectionManager()
    {
        return self::$serviceContainer->get('neoclient.connection_manager');
    }

    /**
     * Returns the connection bound to the alias, or the default connection if no alias is provided.
     *
     * @param string|null $alias
     *
     * @return \Neoxygen\NeoClient\Connection\Connection The connection with alias "$alias"
     */
    public function getConnection($alias = null)
    {
        return $this->getConnectionManager()->getConnection($alias);
    }

    /**
     * Returns the CommandManager Service.
     *
     * @return \Neoxygen\NeoClient\Command\CommandManager
     */
    public function getCommandManager()
    {
        return self::$serviceContainer->get('neoclient.command_manager');
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer()
    {
        return self::$serviceContainer;
    }

    /**
     * @param $method
     * @param $attributes
     *
     * @return \Neoxygen\NeoClient\Request\Response
     */
    public function __call($method, $attributes)
    {
        $extManager = $this->getServiceContainer()->get('neoclient.extension_manager');

        $response = $extManager->execute($method, $attributes);

        $this->lastResponse = $response;

        return $response;
    }

    /**
     * @return \Neoxygen\NeoClient\Request\Response
     */
    public function getResponse()
    {
        return $this->lastResponse;
    }

    /**
     * @return \Neoxygen\NeoClient\Formatter\Result
     */
    public function getResult()
    {
        return $this->lastResponse->getResult();
    }

    /**
     * @return array|null
     */
    public function getRows()
    {
        return $this->lastResponse->getRows();
    }

    public static function log($level = 'debug', $message, array $context = array())
    {
        return self::$logger->log($level, $message, $context);
    }
}
