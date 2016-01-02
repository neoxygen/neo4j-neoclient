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
        $q = 'MATCH (campaign:Campaign)-[created_for: CREATED_FOR]->(facebookPage:FacebookPage) ,
            (campaign)-[happens_at:HAPPENS_AT]->(location:Location)
            OPTIONAL MATCH (facebookAlbum:FacebookAlbum)-[opened_for:OPENED_FOR]-(campaign)
            RETURN created_for,happens_at,opened_for,campaign,facebookPage,location,
            collect (facebookAlbum) AS facebookAlbums LIMIT 25';

        $result = $this->getConnection()->sendCypherQuery($q)->getResult();
        $this->assertInstanceOf(Node::class, $result->get('facebookAlbums'));

    }

    public function prepareDB()
    {
        $this->getConnection()->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
        $prepare = 'CREATE (c:Campaign)-[:CREATED_FOR]->(f:FacebookPage), (c)-[:HAPPENS_AT]->(l:Location)';
        $this->getConnection()->sendCypherQuery($prepare);
        $p2 = 'CREATE (c:Campaign)-[:CREATED_FOR]->(f:FacebookPage), (c)-[:HAPPENS_AT]->(l:Location), (fa:FacebookAlbum)-[:OPENED_FOR]->(c)';
        $this->getConnection()->sendCypherQuery($p2);
    }
}