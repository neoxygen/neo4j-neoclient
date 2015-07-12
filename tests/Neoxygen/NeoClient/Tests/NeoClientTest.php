<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ClientBuilder;

class NeoClientTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildClient()
    {
        $client = ClientBuilder::create()
            ->build();

        $this->assertInstanceOf('Neoxygen\NeoClient\Client', $client);
    }

    public function testAddConnection()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, '', '4287e44985b04c7536c523ca6ea8e67c')
            ->build();

        $this->assertTrue($client->getConnectionManager()->hasConnection('default'));
        $this->assertInstanceOf('Neoxygen\NeoClient\Connection\Connection', $client->getConnection('default'));
        $conn = $client->getConnection('default');
        $this->assertEquals('http', $conn->getScheme());
        $this->assertEquals('localhost', $conn->getHost());
        $this->assertEquals(7474, $conn->getPort());
    }
}
