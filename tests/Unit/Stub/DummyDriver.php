<?php

namespace GraphAware\Neo4j\Tests\Unit\Stub;

use GraphAware\Common\Driver\DriverInterface;
use GraphAware\Common\Driver\Protocol;

class DummyDriver implements DriverInterface
{
    protected $uri;

    public function __construct($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    public function session()
    {

    }

}