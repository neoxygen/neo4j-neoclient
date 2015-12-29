<?php

namespace Neoxygen\NeoClient\Tests\Issues;

use Neoxygen\NeoClient\ClientBuilder;
use Neoxygen\NeoClient\Formatter\Node;
use Neoxygen\NeoClient\Formatter\Relationship;

/**
 * Class Issue73
 * @package Neoxygen\NeoClient\Tests\Issues
 *
 * @group issue-73
 */
class Issue73Test extends \PHPUnit_Framework_TestCase
{
    public function getConnection()
    {
        return ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
            ->setAutoFormatResponse(true)
            //->enableNewFormattingService()
            ->build();
    }

    /**
     * https://github.com/neoxygen/neo4j-neoclient/issues/73
     */
    public function testReportedIssue()
    {
        $this->prepareDB();
        $query1 = 'MATCH (n:Node {id:1}) OPTIONAL MATCH (n)-[r]->(o) RETURN n,r,o';
        $result = $this->getConnection()->sendCypherQuery($query1)->getResult();
        $this->assertInstanceOf(Node::class, $result->get('n', true));
        $this->assertInstanceOf(Node::class, $result->get('o', true));
        $this->assertInstanceOf(Relationship::class, $result->get('r', true));

        $query2 = 'MATCH (n:Node {id:3}) OPTIONAL MATCH (n)-[r]->(o) RETURN n,r,o';
        $result2 = $this->getConnection()->sendCypherQuery($query2)->getResult();
        $this->assertInstanceOf(Node::class, $result2->get('n', true));
        $this->assertEquals(null, $result2->get('r'));

    }

    public function prepareDB()
    {
        $this->getConnection()->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
        $prepare = 'CREATE (n:Node {id:1})-[:REL]->(b:Node {id:2}), (c:Node {id:3})';
        $this->getConnection()->sendCypherQuery($prepare);
    }
}