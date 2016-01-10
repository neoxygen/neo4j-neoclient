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

use GraphAware\Bolt\GraphDatabase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use GraphAware\Neo4j\Client\Connection\ConnectionManager;
use GraphAware\Neo4j\Client\Connection\Connection;

class ClientBuilder
{
    protected $config = [];

    protected $connectionManager;

    public function __construct()
    {
        $this->connectionManager = new ConnectionManager();
    }

    public static function create()
    {
        return new self();
    }

    public function addConnection($alias, $uri)
    {
        $this->connectionManager->registerConnection(new Connection($alias, GraphDatabase::driver($uri)));

        return $this;
    }

    public function build()
    {
        return new Client($this->connectionManager, new EventDispatcher());
    }
}
