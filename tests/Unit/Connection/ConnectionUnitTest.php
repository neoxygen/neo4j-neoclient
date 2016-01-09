<?php

namespace GraphAware\Neo4j\Tests\Unit\Connection;

use GraphAware\Common\Driver\Protocol;
use GraphAware\Neo4j\Connection\Connection;
use GraphAware\Neo4j\Tests\Unit\Stub\DummyDriver;

/**
 * @group unit
 * @group connection
 */
class ConnectionUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectionInstantiation()
    {
        $driver = new DummyDriver('localhost', Protocol::HTTPS);
        $connection = new Connection('default', $driver);

        $this->assertEquals($driver, $connection->getDriver());
        $this->assertEquals('default', $connection->getAlias());
    }
}