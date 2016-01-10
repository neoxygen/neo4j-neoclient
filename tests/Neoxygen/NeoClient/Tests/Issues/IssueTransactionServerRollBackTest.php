<?php

namespace Neoxygen\NeoClient\Tests\Issues;

use Neoxygen\NeoClient\ClientBuilder;
use Neoxygen\NeoClient\Exception\Neo4jException;

/**
 * @group issue
 * @group issue-tx
 *
 * https://github.com/neo4j/neo4j/issues/5806
 */
class IssueTransactionServerRollBackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Neoxygen\NeoClient\Client
     */
    public function getConnection()
    {
        return ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
            ->setAutoFormatResponse(true)
            //->enableNewFormattingService()
            ->build();
    }

    public function testIssueDescription()
    {
        $conn = $this->getConnection();
        $conn->createSchemaUniqueConstraint('User', 'name');
        $conn->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
        $conn->sendCypherQuery('CREATE (n:User {name:"Chris"})');

        $tx = $conn->createTransaction();
        try {
            $tx->pushQuery('CREATE (n:User {name:"Chris"})');
        } catch (Neo4jException $e) {
            //
        }

        $this->assertFalse($tx->isActive());

    }
}