<?php

namespace GraphAware\NeoClient\Tests\Formatting;

use Neoxygen\NeoClient\ClientBuilder;

class GAFormattingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group formatter
     */
    public function testFormattingIsEnabled()
    {
        $client = ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
          ->setAutoFormatResponse(true)
          ->enableNewFormattingService()
          ->build();

        $response = $client->sendCypherQuery('MATCH (n) RETURN count(n)');
        $this->assertInstanceOf('GraphAware\NeoClient\Formatter\Response', $response);
    }

    /**
     * @group formatter
     */
    public function testNewFormattingWithLiveTx()
    {
        $client = ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
          ->setAutoFormatResponse(true)
          ->enableNewFormattingService()
          ->build();

        $tx = $client->createTransaction();
        $tx->pushQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
        $result = $tx->pushQuery('MATCH (n) RETURN count(n) as c');
        $tx->commit();

        $this->assertEquals(0, $result->get('c', true));
    }

    /**
     * @group formatter
     */
    public function testNewFormattingWithMultipleStmtsInLiveTx()
    {
        $client = ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
          ->setAutoFormatResponse(true)
          ->enableNewFormattingService()
          ->build();
        $sts = [];
        $sts[] = ['query' => 'CREATE (n:User) RETURN n'];
        $sts[] = ['query' => 'CREATE (n:Book) RETURN n'];
        $tx = $client->createTransaction();
        $results = $tx->pushMultiple($sts);
        $tx->commit();

        $this->assertCount(2, $results);
        $this->assertInstanceOf('GraphAware\NeoClient\Formatter\Graph\Node', $results[0]->get('n', true));
    }
}