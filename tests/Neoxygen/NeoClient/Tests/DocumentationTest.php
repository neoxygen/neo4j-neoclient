<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ClientBuilder;

class DocumentationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the Client
     *
     * You need to call the ClientBuilder create factory method
     * and add a connection by providing an alias and your connection settings
     *
     * This returns you a \Neoxygen\NeoClient\Client object
     */
    public function testBuildClient()
    {
        $client = ClientBuilder::create()
            ->addConnection('default', 'http', 'localhost', 7474)
            ->build();

        $this->assertInstanceOf('Neoxygen\NeoClient\Client', $client);
    }

    /**
     * If you use a Neo4j database in a default local environment, meaning the database runnning at
     * http://localhost:7474 , you can use the addDefaultLocalConnection
     */
    public function testDefaultLocalConnection()
    {
        $client = ClientBuilder::create()
            ->addDefaultLocalConnection()
            ->build();

        $this->assertInstanceOf('Neoxygen\NeoClient\Client', $client);
    }

    /**
     * With the client object you have access to useful methods against your Neo4j database
     *
     * getRoot()
     *
     * Returns you the root endpoint response from the Neo4j ReST API
     */
    public function testGetRoot()
    {
        $client = $this->buildClient();
        $root = $client->getRoot();

        $this->assertArrayHasKey('data', $root);
        $this->assertArrayHasKey('management', $root);

        /**
        Array
        (
        [management] => http://localhost:7474/db/manage/
        [data] => http://localhost:7474/db/data/
        )
         */
    }

    /**
     * getNeo4jVersion()
     *
     * Returns you the version of your Neo4j database
     */
    public function testGetNeo4jVersion()
    {
        $client = $this->buildClient();
        $version = $client->getNeo4jVersion();

        $this->assertTrue($this->checkVersion($version));

        // 2.1.5
    }

    public function testPing()
    {
        $client = $this->buildClient();
        $ping = $client->ping();

        // Returns nothing or throw an Exception if database not reachable
    }

    /**
     * Sends a Cypher Query to the database
     *
     * sendCypherQuery($q, array $params = array(), $conn, array $resultDataContents = array(), $writeMode = true)
     */
    public function testSendQuery()
    {
        $q = 'MATCH (n) RETURN count(n)';
        $client = $this->buildClient();
        $response = $client->sendCypherQuery($q);
    }

    public function testRenameLabel()
    {
        $client = $this->buildClient();
        $q = 'FOREACH (i IN range(0,10) | CREATE (n:Person) )';
        $client->sendCypherQuery($q);

        $client->renameLabel('Person', 'User');
    }

    public function testGetConstraints()
    {
        $client = $this->buildClient();
        $client->createUniqueConstraint('Person', 'email');
        $client->createUniqueConstraint('User', 'username');
        $client->createUniqueConstraint('User', 'email');
        $constraints = $client->getUniqueConstraints();
        $this->assertArrayHasKey('User', $constraints);
        $this->assertArrayHasKey('Person', $constraints);
        $this->assertContains('email', $constraints['Person']);
        $this->assertContains('username', $constraints['User']);
        $this->assertContains('email', $constraints['User']);
    }

    private function checkVersion($v)
    {
        if (preg_match('/2.1/', $v) || preg_match('/2.2/', $v)){
            return true;
        }

        return false;
    }

    /**
     * @return \Neoxygen\NeoClient\Client
     */
    private function buildClient()
    {
        $client = ClientBuilder::create()
            ->addDefaultLocalConnection()
            ->build();

        return $client;
    }
}