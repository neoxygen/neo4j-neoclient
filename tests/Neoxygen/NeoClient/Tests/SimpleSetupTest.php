<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ClientBuilder,
    Neoxygen\NeoClient\Client;

class SimpleSetupTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleBuild()
    {
        $client = ClientBuilder::create()
            ->addDefaultLocalConnection()
            ->build();

        $this->assertTrue($client instanceof Client);
    }

    public function testDefaultConnectionIsRegistered()
    {
        $client = ClientBuilder::create()
            ->addDefaultLocalConnection()
            ->build();

        $cm = $client->getConnectionManager();
        $this->assertTrue($cm->hasConnection('default'));
    }
}