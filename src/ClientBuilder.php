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

class ClientBuilder
{
    const PREFLIGHT_ENV_DEFAULT = 'NEO4J_DB_VERSION';

    /**
     * @var array
     */
    protected $config = [];

    public function __construct()
    {
        $this->config['connection_manager']['preflight_env'] = self::PREFLIGHT_ENV_DEFAULT;
    }

    /**
     * Creates a new Client factory
     *
     * @return \GraphAware\Neo4j\Client\ClientBuilder
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Add a connection to the handled connections
     *
     * @param string $alias
     * @param string $uri
     *
     * @return $this
     */
    public function addConnection($alias, $uri)
    {
        $this->config['connections'][$alias]['uri'] = $uri;

        return $this;
    }

    public function preflightEnv($variable)
    {
        $this->config['connection_manager']['preflight_env'] = $variable;
    }

    /**
     * Builds a Client based on the connections given
     *
     * @return \GraphAware\Neo4j\Client\Client
     */
    public function build()
    {
        $connectionManager = new ConnectionManager();
        foreach ($this->config['connections'] as $alias => $conn) {
            $connectionManager->registerConnection($alias, $conn['uri']);
        }
        return new Client($connectionManager);
    }
}
