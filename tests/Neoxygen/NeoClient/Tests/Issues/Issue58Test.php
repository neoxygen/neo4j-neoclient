<?php

namespace Neoxygen\NeoClient\Tests\Issues;

use Neoxygen\NeoClient\ClientBuilder;

/**
 * Class Issue58Test
 * @package Neoxygen\NeoClient\Tests\Issues
 *
 * @group integration
 * @group issues
 */
class Issue58Test extends \PHPUnit_Framework_TestCase
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
     * @group issues
     */
    public function testReportedIssue()
    {
        $this->emptyDatabase();
        $state = 'CREATE (:Property:Item:OtherLabel {id: 1})';
        $this->prepareDatabase($state);

        $q = 'MATCH (n:Property {id:1}) RETURN n as property, labels(n) as labels';
        $result = $this->getConnection()->sendCypherQuery($q)->getResult();

        $this->assertCount(3, $result->get('labels'));
        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Node', $result->get('property'));
    }

    private function emptyDatabase()
    {
        $this->getConnection()->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');
    }

    private function prepareDatabase($state)
    {
        $this->emptyDatabase();
        $this->getConnection()->sendCypherQuery($state);
    }
}