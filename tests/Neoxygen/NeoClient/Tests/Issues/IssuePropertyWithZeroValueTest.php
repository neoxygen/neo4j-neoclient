<?php

namespace Neoxygen\NeoClient\Tests\Issues;

use Neoxygen\NeoClient\ClientBuilder;

class IssuePropertyWithZeroValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group issue-property
     */
    public function testPropertyWithZeroValueIsFormattedCorrectly()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
            ->setAutoFormatResponse(true)
            ->build();

        $client->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
        $insert = 'CREATE (n:Node {count: 0})';
        $client->sendCypherQuery($insert);

        $q = 'MATCH (n:Node) RETURN n';
        $result = $client->sendCypherQuery($q)->getResult();
        $v = $result->get('n')->getProperty('count');
        $this->assertTrue(0 === $v);
        $this->assertTrue(null === $result->get('n')->getProperty('unk', null));
        $this->setExpectedException('InvalidArgumentException');
        $result->get('n')->getProperty('unk');
    }
}