<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client;

use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use GraphAware\Neo4j\Client\Transaction\Transaction;

class Client
{
    const NEOCLIENT_VERSION = '4.0.0';

    /**
     * @var \GraphAware\Neo4j\Client\Connection\ConnectionManager
     */
    protected $connectionManager;

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * @param $query
     * @param null|array $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return \GraphAware\Common\Result\Result
     */
    public function run($query, $parameters = null, $tag = null, $connectionAlias = null)
    {
        $connection = $this->connectionManager->getConnection($connectionAlias);

        return $connection->run($query, $parameters, $tag);
    }

    /**
     * @param string|null $tag
     *
     * @return \GraphAware\Neo4j\Client\Stack
     */
    public function stack($tag = null, $connectionAlias = null)
    {
        return Stack::create($tag, $connectionAlias);
    }

    /**
     * @param \GraphAware\Neo4j\Client\Stack $stack
     *
     * @return \GraphAware\Common\Result\ResultCollection
     */
    public function runStack(Stack $stack)
    {
        $pipeline = $this->pipeline($stack->getConnectionAlias());
        foreach ($stack->statements() as $statement) {
            $pipeline->push($statement->text(), $statement->parameters(), $statement->getTag());
        }

        return $pipeline->run();
    }

    /**
     * @param null $connectionAlias
     *
     * @return \GraphAware\Neo4j\Client\Transaction\Transaction
     */
    public function transaction($connectionAlias = null)
    {
        $connection = $this->connectionManager->getConnection($connectionAlias);
        $driverTransaction = $connection->getTransaction();

        return new Transaction($driverTransaction);
    }

    /**
     * @param null|string $query
     * @param null|array $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return \GraphAware\Neo4j\Client\HttpDriver\Pipeline
     */
    private function pipeline($query = null, $parameters = null, $tag = null, $connectionAlias = null)
    {
        $connection = $this->connectionManager->getConnection($connectionAlias);

        return $connection->createPipeline($query, $parameters, $tag);
    }

    /**
     * @deprecated since 4.0 - will be removed in 5.0 - use <code>$client->run()</code> instead.
     *
     * @param $query
     * @param null|array $parameters
     * @param null|string $tag
     * @param null|string $connectionAlias
     *
     * @return \GraphAware\Bolt\Result\Result
     */
    public function sendCypherQuery($query, $parameters = null, $tag = null, $connectionAlias = null)
    {
        $connection = $this->connectionManager->getConnection($connectionAlias);

        return $connection->run($query, $parameters, $tag);
    }

    /**
     * @return \GraphAware\Neo4j\Client\Connection\ConnectionManager
     */
    public function getConnectionManager()
    {
        return $this->connectionManager;
    }
}
