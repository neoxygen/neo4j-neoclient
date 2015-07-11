<?php

namespace Neoxygen\NeoClient\Tests\Functional;

use Neoxygen\NeoClient\ClientBuilder;

/**
 * @group functional
 */
class CoreCommandsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Neoxygen\NeoClient\Client
     */
    protected $client;

    public function setUp()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
            ->setAutoFormatResponse(true)
            ->setDefaultTimeout(15)
            ->build();

        $this->client = $client;
    }

    public function testGetRoot()
    {
        $response = $this->client->getRoot();
        $root = $response->getBody();

        $this->assertArrayHasKey('data', $root);
        $this->assertArrayHasKey('management', $root);
    }

    public function testGetLabels()
    {
        $response = $this->client->getLabels();
        $labels = $response->getBody();

        $this->assertInternalType('array', $labels);
    }

    public function testRenameLabel()
    {
        $q = 'MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n';
        $this->client->sendCypherQuery($q);

        $q = 'CREATE (n:OldLabel)';
        $this->client->sendCypherQuery($q);
        $response = $this->client->getLabels();
        $labels = $response->getBody();
        $this->assertContains('OldLabel', $labels);

        $this->client->renameLabel('OldLabel', 'NewLabel');
        $labels = $this->client->getLabels()->getBody();
        $this->assertContains('NewLabel', $labels);
    }

    public function testCreateAndListIndex()
    {
        $this->client->createIndex('User', 'email');
        $response = $this->client->listIndex('User');
        $indexes = $response->getBody();

        $this->assertContains('email', $indexes);
    }

    public function testDropIndex()
    {
        $this->client->createIndex('Drop', 'user');
        $this->assertTrue($this->client->isIndexed('Drop', 'user'));
        $this->client->dropIndex('Drop', 'user');
        $this->assertFalse($this->client->isIndexed('Drop', 'user'));
    }

    public function testListIndexes()
    {
        $this->client->createIndex('List1', 'property');
        $this->client->createIndex('List2', 'property');
        $response = $this->client->listIndexes();
        $indexes = $response->getBody();

        $this->assertArrayHasKey('List1', $indexes);
        $this->assertArrayHasKey('List2', $indexes);
    }

    public function testCreateUniqueConstraint()
    {
        $this->client->createUniqueConstraint('Label', 'uniqueProperty');
        $constraints = $this->client->getUniqueConstraints()->getBody();

        $this->assertArrayHasKey('Label', $constraints);
        $this->assertContains('uniqueProperty', $constraints['Label']);
    }

    public function testDropUniqueConstraint()
    {
        $this->client->createUniqueConstraint('ToDrop', 'username');
        $this->assertArrayHasKey('ToDrop', $this->client->getUniqueConstraints()->getBody());
        $this->client->dropUniqueConstraint('ToDrop', 'username');
        $this->assertArrayNotHasKey('ToDrop', $this->client->getUniqueConstraints()->getBody());
    }

    public function testPushMultipleInTransaction()
    {
        $q = 'MATCH (n:MultiplePersonNode) DELETE n';
        $this->client->sendCypherQuery($q);
        $statements = [];
        $statement = 'CREATE (n:MultiplePersonNode {id:{myId} })';
        for ($i = 0; $i <= 2000; $i++) {
            $statements[] = ['statement' => $statement, 'parameters' => ['myId' => uniqid()]];
        }
        $this->client->sendMultiple($statements);

        $q = 'MATCH (n:MultiplePersonNode) RETURN count(n)';
        $r = $this->client->sendCypherQuery($q);
        $count = $r->getRows()['count(n)'][0];

        $this->assertEquals(2001, $count);
    }


}
