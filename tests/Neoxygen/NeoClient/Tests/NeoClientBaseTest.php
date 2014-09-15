<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\NeoClient;
use Neoxygen\NeoClient\Tests\NeoClientTestCase;

class NeoClientBaseTest extends NeoClientTestCase
{
    public function testLoadingConfig()
    {
        $config = $this->getDefaultConfig();
        NeoClient::getServiceContainer()
            ->loadConfiguration($config)
            ->build();

        $root = json_decode(NeoClient::getRoot(), true);

        $this->assertEquals(2, count($root));
    }


}