<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Connection;

use Neoxygen\NeoClient\Exception\InvalidConnectionException;
use Neoxygen\NeoClient\Exception\HttpException;

class ConnectionManager
{
    /**
     * @var array Array of all registered connections
     */
    private $connections = [];

    public function registerConnection($alias, $uri, $config = null)
    {
        $this->connections[$alias] = new Connection($alias, $uri, $config);
    }

    /**
     * @param null $alias
     * @return \GraphAware\Neo4j\Client\Connection\Connection
     */
    public function getConnection($alias = null)
    {
        if (null === $alias) {
            list($a) = array_keys($this->connections);
            return $this->connections[$a];
        }

        if (!array_key_exists($alias, $this->connections)) {
            throw new \InvalidArgumentException(sprintf('The connection "%s" is not registered', $alias));
        }

        return $this->connections[$alias];
    }
}
