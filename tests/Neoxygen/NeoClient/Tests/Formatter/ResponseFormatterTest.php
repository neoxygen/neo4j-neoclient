<?php

namespace Neoxygen\NeoClient\Tests\Formatter;

use Neoxygen\NeoClient\Tests\NeoClientTestCase;
use Neoxygen\NeoClient\Formatter\ResponseFormatter;

class ResponseFormatterTest extends NeoClientTestCase
{

    public function testResponseIsFormatted()
    {
        $client = $this->build();
        $this->setUpMovieDb($client);

        $q = 'MATCH (n:Actor) RETURN n';
        $client->sendCypherQuery($q, array(), null, array('graph'));
        $result = $client->getResult();

        $this->assertTrue(3 === $result->getNodesCount());
        $this->assertTrue(0 === $result->getRelationshipsCount());
        foreach ($result->getNodes() as $node) {
            $this->assertTrue($node->hasLabel('Actor'));
        }

        $single = $result->getSingleNode();
        $this->assertEquals('Actor', $single->getLabel());
        $this->assertCount(1, $single->getLabels());
        $this->assertTrue($single->hasProperty('name'));
        $this->assertFalse($single->hasProperty('firstname'));
    }

    public function testAdvancedFormatting()
    {
        $client = $this->build();
        $this->setUpMovieDb($client);

        $q = 'MATCH p=(a:Actor)-[]->(m:Movie) RETURN p';
        $client->sendCypherQuery($q, array(), null, array('graph'));

        $result = $client->getResult();

        $this->assertCount(3, $result->getNodesByLabel('Actor'));
        $this->assertCount(3, $result->getNodesByLabel('Movie'));

        $actor = $result->getSingleNodeByLabel('Actor');
        $this->assertTrue($actor->hasProperty('name'));
        $this->assertCount(3, $actor->getOutboundRelationships());
        foreach ($actor->getRelationships() as $rel){
            $this->assertTrue($rel->hasProperty('role'));
            $this->assertTrue($rel->getEndNode()->hasLabel('Movie'));
            $this->assertEquals('ACTS_IN', $rel->getType());
            $this->assertTrue(in_array($rel->getProperty('role'), array('Trinity', 'Morpheus', 'Neo')));
            $this->assertNull($rel->getProperty('level'));
        }
        $this->assertCount(3, $actor->getRelationships('ACTS_IN', 'OUT'));
        $this->assertEmpty($actor->getRelationships('BAD_TYPE'));
        $this->assertEmpty($actor->getRelationships('ACTS_IN', 'IN'));
        $this->assertCount(3, $actor->getRelationships('ACTS_IN'));
        $this->assertTrue($actor->getSingleRelationship('ACTS_IN')->hasProperty('role'));
        $this->assertEmpty($actor->getSingleRelationship('BAD_TYPE'));
        $this->assertEmpty($actor->getSingleRelationship('ACTS_IN', 'IN'));
        $this->assertEmpty($actor->getInboundRelationships());

        $movie = $result->getSingleNode('Movie');
        $this->assertTrue($movie->hasProperty('title'));
        $this->assertTrue($movie->hasProperty('year'));
        $this->assertEquals('Movie', $movie->getLabel());
        $this->assertEmpty($result->getSingleNode('Singer'));

        // Test multiple properties request
        $props = $movie->getProperties(array('title'));
        $this->assertArrayHasKey('title', $props);
        $this->assertTrue(in_array($props['title'], array('The Matrix', 'The Matrix Reloaded', 'The Matrix Revolutions')));
        $props2 = $movie->getProperties(array('title', 'year', 'nonExistent'));
        $this->assertNotNull($props2['year']);
        $this->assertNotNull($props2['title']);
        $this->assertNull($props2['nonExistent']);

    }

    private function setUpMovieDb($client)
    {

        // Clearing the database

        $q = 'MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n';
        $client->sendCypherQuery($q);

        $importQuery = "CREATE (matrix1:Movie { title : 'The Matrix', year : '1999-03-31' })
CREATE (matrix2:Movie { title : 'The Matrix Reloaded', year : '2003-05-07' })
CREATE (matrix3:Movie { title : 'The Matrix Revolutions', year : '2003-10-27' })
CREATE (keanu:Actor { name:'Keanu Reeves' })
CREATE (laurence:Actor { name:'Laurence Fishburne' })
CREATE (carrieanne:Actor { name:'Carrie-Anne Moss' })
CREATE (keanu)-[:ACTS_IN { role : 'Neo' }]->(matrix1)
CREATE (keanu)-[:ACTS_IN { role : 'Neo' }]->(matrix2)
CREATE (keanu)-[:ACTS_IN { role : 'Neo' }]->(matrix3)
CREATE (laurence)-[:ACTS_IN { role : 'Morpheus' }]->(matrix1)
CREATE (laurence)-[:ACTS_IN { role : 'Morpheus' }]->(matrix2)
CREATE (laurence)-[:ACTS_IN { role : 'Morpheus' }]->(matrix3)
CREATE (carrieanne)-[:ACTS_IN { role : 'Trinity' }]->(matrix1)
CREATE (carrieanne)-[:ACTS_IN { role : 'Trinity' }]->(matrix2)
CREATE (carrieanne)-[:ACTS_IN { role : 'Trinity' }]->(matrix3)";

        $client->sendCypherQuery($importQuery);
    }
}