<?php

namespace Neoxygen\NeoClient\Tests\Functional;

use Neoxygen\NeoClient\ClientBuilder;

/**
 * @group functional
 */
class UseCaseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $client = $this->getClient();

        $client->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
    }

    public function testConnectivity()
    {
        $client = $this->getClient();
        $root = $client->getRoot()->getBody();

        $this->assertArrayHasKey('data', $root);
        $this->assertArrayHasKey('management', $root);
    }

    public function testCreatingElement()
    {
        $client = $this->getClient();
        $q = 'CREATE (u:`User` {name: {name}, email: {email}}) RETURN u';
        $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com'];
        $response = $client->sendCypherQuery($q, $params);
        $result = $response->getResult();


        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result',$result);
        // The user is getting created successfully but never returned afterwards.
        $this->assertArrayHasKey('User', $result->getNodesByLabel('User', true));
    }

    public function testCreatingElementsAndRelations()
    {
        $client = $this->getClient();
        $q = 'CREATE (u:`User` {name: {name}, email: {email}})-[:LIKES]->(p:`Post` {title: {title}}) RETURN u, p';
        $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com', 'title' => 'Sss'];
        $response = $client->sendCypherQuery($q, $params);
        $result = $response->getResult();

        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result',$result);

        $nodes = $result->getNodes(['User', 'Post'], true);

        $this->assertArrayHasKey('User', $nodes);
        $this->assertArrayHasKey('Post', $nodes);

        $nodes_too = $result->getNodesByLabels(['User', 'Post'], true);
        $this->assertArrayHasKey('User', $nodes_too);
        $this->assertArrayHasKey('Post', $nodes_too);
    }

    public function testFetchingElementByAttribute()
    {
        $client = $this->getClient();
        // You need to recreate the user here because you delete the db on each test run with the query in the setUp() method
        $q = 'CREATE (u:`User` {name: {name}, email: {email}})-[:LIKES]->(p:`Post` {title: {title}}) RETURN u, p';
        $params = ['name' => 'Abed Halawi', 'email' => 'halawi.abed@gmail.com', 'title' => 'Sss'];
        $client->sendCypherQuery($q, $params);

        $q = 'MATCH (u:`User`) WHERE u.email = {email} RETURN u';

        $response = $client->sendCypherQuery($q, ['email' => 'halawi.abed@gmail.com']);
        $result = $response->getResult();

        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result', $result);
        $this->assertGreaterThan(0, $result->getNodesCount());

        $node = current($result->getNodesByLabel('User'));

        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Node', $node);
        $props = $node->getProperties(['name']);
        $this->assertEquals(['name' => 'Abed Halawi'], $props);
    }

    public function testGettingAndRenamingLabels()
    {
        $client = $this->getClient();

        $result = $client->sendCypherQuery('CREATE (n:Order) RETURN n')->getResult();
        $this->assertTrue(in_array('Order', $client->getLabels()->getBody()));

        $client->renameLabel('Order', 'Product');

        $updated_labels = $client->getLabels()->getBody();
        $this->assertTrue(in_array('Product', $updated_labels));
    }

    public function testIndexing()
    {
        $client = $this->getClient();

        $this->assertTrue($client->createIndex('Person', 'email', 'name'));
    }

    public function testRelationshipProperty()
    {
        $client = $this->getClient();
        $q = 'CREATE (n:RelationshipTest)-[r:SOME_REL {time:1234} ]->(x:RelTest) RETURN r';
        $r = $client->sendCypherQuery($q)->getResult();
        $rel = $r->get('r');
        $this->assertEquals(1234, $rel->getProperty('time'));
        $this->assertNull($rel->getProperty('nonExist'));
    }

    public function testFormatterIsResetBetweenQueries()
    {
        $client = $this->getClient();
        $q1 = "CREATE (n:FormatterTest {name:'User1'})";
        $q2 = "CREATE (n:FormatterTest {name:'User2'})";
        $client->sendCypherQuery($q1);
        $client->sendCypherQuery($q2);
        $match1 = "MATCH (n:FormatterTest {name:'User1'}) RETURN n";
        $match2 = "MATCH (n:FormatterTest {name:'User2'}) RETURN n";
        $r1 = $client->sendCypherQuery($match1)->getResult();
        $this->assertCount(1, $r1->getNodes());
        $r2 = $client->sendCypherQuery($match2)->getResult();
        $this->assertCount(1, $r2->getNodes());
        $this->assertCount(1, $r1->getnodes());

    }

    public function testCreateTransaction()
    {
        $client = $this->getClient();
        $q = 'FOREACH (i in range(0,24)| CREATE (n:Person {id: i} ))';
        $client->sendCypherQuery($q);
        $query = 'MATCH (n:`Person`) RETURN n LIMIT 25';

        $tx = $client->createTransaction();
        $tx->pushQuery($query);
        $tx->commit();
        $this->assertCount(1, $tx->getResults());
        $this->assertCount(25, $tx->getResults()[0]->getNodes());

    }

    protected function getClient()
    {
        return ClientBuilder::create()
            ->addConnection('default','http','localhost',7474, true, '', '4287e44985b04c7536c523ca6ea8e67c')
            ->setAutoFormatResponse(true)
            ->build();
    }
}