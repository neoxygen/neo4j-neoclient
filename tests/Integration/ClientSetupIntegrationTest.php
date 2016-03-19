<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Tests\Integration;

use GraphAware\Neo4j\Client\Client;
use GraphAware\Neo4j\Client\ClientBuilder;
use GraphAware\Neo4j\Client\HttpDriver\Driver as HttpDriver;
use GraphAware\Bolt\Driver as BoltDriver;

class ClientSetupIntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testClientSetupWithOneConnection()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'bolt://localhost')
            ->build();

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testHttpDriverIsUsedForConnection()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http://localhost:7474')
            ->build();

        $connection = $client->getConnectionManager()->getConnection('default');
        $this->assertInstanceOf(HttpDriver::class, $connection->getDriver());
    }

    public function testBoltDriverIsUsedForConnection()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'bolt://localhost')
            ->build();

        $connection = $client->getConnectionManager()->getConnection('default');
        $this->assertInstanceOf(BoltDriver::class, $connection->getDriver());
    }

    public function testTwoConnectionCanBeUsed()
    {
        $client = ClientBuilder::create()
            ->addConnection('http', 'http://localhost:7474')
            ->addConnection('bolt', 'bolt://localhost')
            ->build();

        $this->assertInstanceOf(HttpDriver::class, $client->getConnectionManager()->getConnection('http')->getDriver());
        $this->assertInstanceOf(BoltDriver::class, $client->getConnectionManager()->getConnection('bolt')->getDriver());
    }
}