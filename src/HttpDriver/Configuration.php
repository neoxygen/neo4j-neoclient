<?php

namespace GraphAware\Neo4j\Client\HttpDriver;

use GraphAware\Common\Driver\ConfigInterface;

class Configuration implements ConfigInterface
{
    /**
     * @var int
     */
    protected $timeout;

    /**
     * @param int $timeout
     */
    public function __construct($timeout)
    {
        $this->timeout = (int) $timeout;
    }

    /**
     * @param int $timeout
     *
     * @return Configuration
     */
    public static function withTimeout($timeout)
    {
        return new self($timeout);
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}
