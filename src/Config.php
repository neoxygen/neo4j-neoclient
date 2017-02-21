<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client;

use GraphAware\Neo4j\Client\HttpDriver\Driver;

class Config
{
    protected $defaultHttpPort = Driver::DEFAULT_HTTP_PORT;

    protected $defaultTcpPort = 8687;

    /**
     * @return Config
     */
    public static function create()
    {
        return new self();
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function withDefaultHttpPort($port)
    {
        $this->defaultHttpPort = (int) $port;

        return $this;
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function withDefaultTcpPort($port)
    {
        $this->defaultTcpPort = (int) $port;

        return $this;
    }
}
