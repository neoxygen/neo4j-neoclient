<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ClientBuilder;

class ClientBuildTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleBuild()
    {
        $client = ClientBuilder::create()
            ->build();

        $this->assertInstanceOf('Neoxygen\NeoClient\Client', $client);
    }

    public function testAddConnection()
    {
        $builder = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'neoclient');

        $this->assertArrayHasKey('default', $builder->getConfiguration()['connections']);
    }

    public function testHAMode()
    {
        $builder = ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'neo4j')
          ->enableHAMode();

        $this->assertEquals(true, $builder->getConfiguration()['ha_mode']['enabled']);
        $this->assertEquals('enterprise', $builder->getConfiguration()['ha_mode']['type']);
    }
}