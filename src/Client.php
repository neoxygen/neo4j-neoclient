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

use GraphAware\Bolt\Exception\MessageFailureException;
use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Client
{
    const NEOCLIENT_VERSION = '4.0.0';

    protected $connectionManager;

    protected $eventDispatcher;

    public function __construct(ConnectionManager $connectionManager, EventDispatcher $eventDispatcher)
    {
        $this->connectionManager = $connectionManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param $query
     * @param null $parameters
     * @param null $tag
     * @param null $connectionAlias
     *
     * @return \GraphAware\Bolt\Result\Result
     */
    public function run($query, $parameters = null, $tag = null, $connectionAlias = null)
    {
        $connection = $this->connectionManager->getConnection($connectionAlias);

        $session = $connection->getDriver()->session();
        $params = is_array($parameters) ? $parameters : array();

        try {
            return $session->run($query, $params);
        } catch (\Exception $e) {
            if ($e instanceof MessageFailureException) {
                $exc = new Neo4jException($e->getMessage());
                $exc->setNeo4jStatusCode($e->getStatusCode());

                throw $exc;
            } else {
                throw $e;
            }
        }
    }
}
