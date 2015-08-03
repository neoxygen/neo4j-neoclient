<?php

namespace Neoxygen\NeoClient\Tests\Issues;

use GraphAware\Neo4j\GraphUnit\Neo4jGraphDatabaseTestCase;

class Issue58Test extends Neo4jGraphDatabaseTestCase
{
    public function getConnection()
    {
        return $this->createConnection('localhost', 7474, 'neo4j', 'veryCoolMax');
    }

    /**
     * @group issues
     */
    public function testReportedIssue()
    {
        $this->emptyDatabase();
        $state = '(:Property:Item:OtherLabel {id: 1})';
        $this->prepareDatabase($state);

        $q = 'MATCH (n:Property {id:1}) RETURN n as property, labels(n) as labels';
        $result = $this->getConnection()->sendCypherQuery($q)->getResult();

        $this->assertCount(3, $result->get('labels'));
        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Node', $result->get('property'));
    }
}