<?php

namespace GraphAware\Neo4j\Client\Tests\Issues;

use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;

/**
 *
 * @group issue-105
 */
class Issue105Test extends IntegrationTestCase
{
    public function testMatchNodeCreatedEarlierInStackWithEmptyDbState()
    {
        $this->emptyDb();
        $stack = $this->client->stack();
        $stack->push('CREATE (n:Node {id:1})');
        $stack->push('MATCH (n:Node {id: 1}) CREATE (n2:Node {id: 2}) MERGE (n)-[r:RELATES]->(n2) RETURN id(r)');
        $results = $this->client->runStack($stack);

        $this->assertCount(2, $results);
        $relId = $results->results()[1]->firstRecord()->get('id(r)');
        $this->assertNotNull($relId);
    }

    public function testMatchNodeFromDbInAStack()
    {
        $this->emptyDb();
        // Create Region node
        $this->client->run('CREATE (n:Region {name: "Picardie"})');

        // Create stack, create department in first push, match department and region and relates them in second push
        $stack = $this->client->stack();
        $stack->push('CREATE (d:Department {name:"Somme"})');
        $stack->push('MATCH (d:Department {name:"Somme"}), (r:Region {name:"Picardie"}) MERGE (d)-[:IN_REGION]->(r)');
        $this->client->runStack($stack);

        // Assert that the relationship is in the graph after stack execution

        $result = $this->client->run('MATCH (n:Department {name:"Somme"})-[r:IN_REGION]->(re:Region {name:"Picardie"}) RETURN n, r, re');
        $this->assertEquals(1, $result->size());
        $this->assertEquals('Picardie', $result->firstRecord()->nodeValue('re')->value('name'));
    }
}