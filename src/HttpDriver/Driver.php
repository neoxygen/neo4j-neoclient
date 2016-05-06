<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace GraphAware\Neo4j\Client\HttpDriver;

use GraphAware\Common\Driver\DriverInterface;
use GuzzleHttp\Client;

class Driver implements DriverInterface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @param string        $uri
     * @param Configuration $config
     */
    public function __construct($uri, Configuration $config)
    {
        $this->uri = $uri;
        $this->config = $config;
    }

    /**
     * @return Session
     */
    public function session()
    {
        return new Session($this->uri, new Client(['timeout' => $this->config->getTimeout()]), $this->config);
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
}
