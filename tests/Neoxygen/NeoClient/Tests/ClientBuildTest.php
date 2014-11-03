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
            ->addConnection('default', 'http', 'localhost', 7474);

        $this->assertArrayHasKey('default', $builder->getConfiguration()['connections']);
    }
}