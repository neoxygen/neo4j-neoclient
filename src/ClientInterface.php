<?php namespace GraphAware\Neo4j\Client;

use GraphAware\Common\Result\AbstractRecordCursor;
use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Result\ResultCollection;
use GraphAware\Neo4j\Client\Schema\Label;
use GraphAware\Neo4j\Client\Transaction\Transaction;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface ClientInterface
 * @package GraphAware\Neo4j\Client
 */
interface ClientInterface
{
    /**
     * Run a Cypher statement against the default database or the database specified.
     *
     * @param $query
     * @param null|array $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return \GraphAware\Common\Result\Result
     *
     * @throws \GraphAware\Neo4j\Client\Exception\Neo4jExceptionInterface
     */
    public function run($query, $parameters = null, $tag = null, $connectionAlias = null);

    /**
     * @param string $query
     * @param null|array $parameters
     * @param null|string $tag
     *
     * @return AbstractRecordCursor
     *
     * @throws Neo4jException
     */
    public function runWrite($query, $parameters = null, $tag = null);

    /**
     * @deprecated since 4.0 - will be removed in 5.0 - use <code>$client->runWrite()</code> instead.
     *
     * @param string $query
     * @param null|array $parameters
     * @param null|string $tag
     *
     * @return AbstractRecordCursor
     *
     * @throws Neo4jException
     */
    public function sendWriteQuery($query, $parameters = null, $tag = null);

    /**
     * @param string|null $tag
     * @param string|null $connectionAlias
     *
     * @return Stack
     */
    public function stack($tag = null, $connectionAlias = null);

    /**
     * @param StackInterface $stack
     *
     * @return ResultCollection|null
     *
     * @throws Neo4jException
     */
    public function runStack(StackInterface $stack);

    /**
     * @param null|string $connectionAlias
     *
     * @return Transaction
     */
    public function transaction($connectionAlias = null);

    /**
     * @param string|null $conn
     * @return Label[]
     */
    public function getLabels($conn = null);

    /**
     * @deprecated since 4.0 - will be removed in 5.0 - use <code>$client->run()</code> instead.
     *
     * @param string $query
     * @param null|array $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return AbstractRecordCursor
     */
    public function sendCypherQuery($query, $parameters = null, $tag = null, $connectionAlias = null);

    /**
     * @return ConnectionManager
     */
    public function getConnectionManager();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
}