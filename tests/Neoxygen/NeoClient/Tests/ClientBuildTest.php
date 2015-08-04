<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ClientBuilder;

class ClientBuildTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group config
     */
    public function testSimpleBuild()
    {
        $client = ClientBuilder::create()
            ->build();

        $this->assertInstanceOf('Neoxygen\NeoClient\Client', $client);
    }

    /**
     * @group config
     */
    public function testAddConnection()
    {
        $builder = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'neoclient');

        $this->assertArrayHasKey('default', $builder->getConfiguration()['connections']);
    }

    /**
     * @group config
     */
    public function testHAMode()
    {
        $builder = ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'neo4j')
          ->enableHAMode();

        $this->assertEquals(true, $builder->getConfiguration()['ha_mode']['enabled']);
        $this->assertEquals('enterprise', $builder->getConfiguration()['ha_mode']['type']);
    }

    /**
     * @group config
     */
    public function testWithConfigArrayLoading()
    {
        $config = array(
            'connections' => array(
                'default' => array(
                    'scheme' => 'http',
                    'host' => 'localhost',
                    'port' => 7474,
                    'auth' => true,
                    'user' => 'neo4j',
                    'password' => 'password'
                )
            ),
            'ha_mode' => array(
                'type' => 'enterprise',
                'enabled' => true,
                'master' => 'default'
            )
        );

        $builder = ClientBuilder::create()
          ->loadConfiguration($config);

        $this->assertArrayHasKey('default', $builder->getConfiguration()['connections']);

        $client = ClientBuilder::create()
          ->loadConfiguration($config)
          ->build();

        $conn = $client->getConnectionManager()->getWriteConnection();
        $this->assertEquals('password', $conn->getAuthPassword());
    }
}