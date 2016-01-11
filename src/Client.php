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
     * @return \GraphAware\Bolt\Result\Result
     */
    public function run($query, $parameters = null, $tag = null, $connectionAlias = null)
    {
        $connection = $this->connectionManager->getConnection($connectionAlias);

        return $connection->run($query, $parameters, $tag);
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
}
