<?php

namespace Neoxygen\NeoClient\Tests\Issues;

use Neoxygen\NeoClient\ClientBuilder;

/**
 * Class IssueSOResultDuplicateTest
 * @package Neoxygen\NeoClient\Tests\Issues
 *
 * @group issues
 * @group integration
 */
class IssueSOResultDuplicateTest extends \PHPUnit_Framework_TestCase
{
    protected $client;

    public function setUp()
    {
        $this->client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
            ->setAutoFormatResponse(true)
            ->build();

        // empty db
        $this->client->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
    }

    public function testSubsequentResultsDoNotDuplicateThePreviousOne()
    {
        // Create user nodes
        $this->client->sendCypherQuery('CREATE (:User {id:1}), (:User {id:2})');

        // Create folder nodes
        $this->client->sendCypherQuery('CREATE (:Folder {id: 1}), (:Folder {id:2})');

        $result1 = $this->client->sendCypherQuery('MATCH (n:User) RETURN n')->getResult();

        $result2 = $this->client->sendCypherQuery('MATCH (n:Folder) RETURN n')->getResult();

        $this->assertCount(2, $result1->getNodes());
        $this->assertCount(2, $result2->getNodes());
    }
}