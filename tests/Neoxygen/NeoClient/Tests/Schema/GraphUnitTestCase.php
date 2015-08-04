<?php

namespace Neoxygen\NeoClient\Tests\Schema;

use Neoxygen\NeoClient\ClientBuilder;

class GraphUnitTestCase
{
    public function getConnection()
    {
        return ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
          ->setAutoFormatResponse(true)
          ->enableNewFormattingService()
          ->build();
    }
}