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

    protected $config = [];

    protected $connectionManager;

    public function __construct()
    {
        $this->config['connection_manager']['preflight_env'] = self::PREFLIGHT_ENV_DEFAULT;
    }

    public static function create()
    {
        return new self();
    }

    public function addConnection($alias, $uri)
    {
        $this->config['connections'][$alias]['uri'] = $uri;

        return $this;
    }

    public function preflightEnv($variable)
    {
        $this->config['connection_manager']['preflight_env'] = $variable;
    }

    public function build()
    {
        $this->connectionManager = new ConnectionManager();
        foreach ($this->config['connections'] as $alias => $conn) {
            $this->connectionManager->registerConnection($alias, $conn['uri']);
        }
        return new Client($this->connectionManager);
    }
}
