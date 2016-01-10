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

    public static function create()
    {

    }
}
