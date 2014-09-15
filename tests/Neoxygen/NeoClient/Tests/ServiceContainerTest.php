<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ServiceContainer;
use Symfony\Component\Yaml\Yaml;

class ServiceContainerTest extends NeoClientTestCase
{
    public function testServicesAreLoaded()
    {
        $config = $this->getDefaultConfig();
        $sc = new ServiceContainer();
        $sc->loadConfiguration($config)
            ->build();

        $this->assertTrue($sc->getServiceContainer()->hasDefinition('neoclient.connection_manager'));
        $this->assertTrue($sc->getServiceContainer()->hasDefinition('neoclient.command_manager'));

        $this->assertInstanceOf('Neoxygen\NeoClient\Command\CommandManager', $sc->getCommandManager());
        $this->assertInstanceOf('Neoxygen\NeoClient\Connection\ConnectionManager', $sc->getConnectionManager());
        $this->assertInstanceOf('Neoxygen\NeoClient\HttpClient\GuzzleHttpClient', $sc->getHttpClient());
    }

    public function testLoadedConfig()
    {
        $config = $this->getDefaultConfig();
        $sc = new ServiceContainer();
        $sc->loadConfiguration($config);

        $this->assertEquals(Yaml::parse($config), $sc->getLoadedConfig());
    }

    public function testGetConnection()
    {
        $sc = $this->build();
        $conn = $sc->getConnection();
        $this->assertEquals('http', $conn->getScheme());
        $this->assertEquals('localhost', $conn->getHost());
        $this->assertEquals(7474, $conn->getPort());
    }


}