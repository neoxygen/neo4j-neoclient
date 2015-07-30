<?php

namespace Neoxygen\NeoClient\Tests\Functional;

use Neoxygen\NeoClient\ClientBuilder;
use Neoxygen\NeoClient\Exception\Neo4jException;
use Neoxygen\NeoClient\Tests\Schema\GraphUnitTestCase;

/**
 * @group functional
 */
class UseCaseTest extends GraphUnitTestCase
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

    public function testReuseNodeIdForOtherQuery()
    {
        $client = $this->getClient();
        $q = 'FOREACH (i in range(0,24)| CREATE (n:Person {id: i} ))';
        $client->sendCypherQuery($q);
        $q2 = 'MATCH (n:Person) RETURN n LIMIT 1';
        $r = $client->sendCypherQuery($q2)->getResult();
        $node = $r->get('n');
        $r2 = $client->sendCypherQuery('
    MATCH (a:Person)
    WHERE id(a) = {id}
    RETURN a
', array(
            'id' => $node->getId()
        ))->getResult();

        $this->assertEquals($node->getId(), $r2->get('a')->getId());
    }

    public function testGetReturnsArray()
    {
        $client = $this->getClient();
        $q = 'FOREACH (i in range(0,24)| CREATE (n:Person {id: i} ))';
        $client->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
        $client->sendCypherQuery($q);
        $result = $client->sendCypherQuery('MATCH (n:Person) RETURN n.id as pId')->getResult();
        $this->assertCount(25, $result->get('pId'));

    }

    public function testGetConnectedNodes()
    {
        $client = $this->getClient();
        $q = 'CREATE (a:Connected {id:1}), (b:Connected {id:2}), (c:Connected {id:3})
        MERGE (a)-[:LINKS_TO]->(b)
        MERGE (b)-[:LINKS_TO]->(c)
        MERGE (c)-[:LINKS_TO]->(a)';
        $client->sendCypherQuery($q);
        $q = 'MATCH (a:Connected {id:1}) OPTIONAL MATCH (a)-[r]-(b) RETURN a,r,b';
        $result = $client->sendCypherQuery($q)->getResult();
        $a = $result->get('a');
        $this->assertTrue($a->hasConnectedNodes());
        $b = $a->getConnectedNode('OUT');
        $this->assertEquals(2, $b->getProperty('id'));
        $this->assertTrue($b->hasConnectedNodes());
    }

    public function testIdentifierWithDotIsHandled()
    {
        $client = $this->getClient();
        $result = $client->sendCypherQuery('
    CREATE (a:TEST {foo: {bar}})
    RETURN a.foo
', array(
            'bar' => array(1, 2, 3)
        ))->getResult();

        $this->assertNotEmpty($result->get('a.foo'));
        $this->assertInternalType('array', $result->get('a.foo'));
    }

    public function testItCanAddAConstraintIfIndexAlreadyExist()
    {
        $client = $this->getClient();
        try {
            $client->createIndex('Tag', 'name');
        } catch (Neo4jException $e) {

        }
        $this->assertTrue($client->createUniqueConstraint('Tag', 'name', true));
    }

    /**
     * @return \Neoxygen\NeoClient\Client
     */
    protected function getClient()
    {
        return $this->getConnection();
    }
}
