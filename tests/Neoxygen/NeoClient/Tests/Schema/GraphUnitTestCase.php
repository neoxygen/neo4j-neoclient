<?php

namespace Neoxygen\NeoClient\Tests\Schema;

use GraphAware\Neo4j\GraphUnit\Neo4jGraphDatabaseTestCase;

class GraphUnitTestCase extends Neo4jGraphDatabaseTestCase
{
    public function getConnection()
    {
        return $this->createConnection('localhost', 7474, 'neo4j', 'veryCoolMax');
    }
}