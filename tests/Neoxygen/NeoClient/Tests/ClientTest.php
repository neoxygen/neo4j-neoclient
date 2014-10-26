<?php

namespace Neoxygen\NeoClient\Tests;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Neoxygen\NeoClient\Client;
use Neoxygen\NeoClient\ClientBuilder;
use Neoxygen\NeoClient\Formatter\ResponseFormatter;
use Symfony\Component\Yaml\Yaml;

class ClientTest extends NeoClientTestCase
{
    /**

    public function testEventListenerIsAdded()
    {
        $client = new Client();
        $client->addEventListener('foo.event', function($event) {});

        $this->assertCount(1, $client->getListeners());
        $client->build();
    }

    public function testLoggersAreRegistered()
    {
        $client = new Client();
        $logger = new Logger('default');
        $handler = new NullHandler(Logger::DEBUG);
        $logger->pushHandler($handler);
        $client->setLogger('default', $logger);

        $this->assertCount(1, $client->getLoggers());
    }


    public function testConnectionsAreRegistered()
    {
        $client = new Client();
        $client->addConnection('default', 'http', 'localhost', 7474)
            ->addConnection('second', 'https', 'localhost', 7575)
            ->build();

        $cm = $client->getConnectionManager();

        $this->assertCount(2, $cm->getConnections());
        $this->assertEquals('default', $cm->getConnection('default')->getAlias());
        $this->assertEquals('default', $client->getConnection('default')->getAlias());
    }

    public function testSetReturnFormat()
    {
        $client = new Client();
    }

    public function testLoadConfigFile()
    {
        $file = $this->getDefaultConfig();
        $client = new Client();
        $client->loadConfigurationFile($file);
        $client->build();
    }

    public function testNullLoggerIsSetIfNoLoggerExist()
    {
        $client = new Client();
        $loggers = $client->getLoggers();

        $this->assertCount(1, $loggers);
    }

    public function testLogEntry()
    {
        $client = new Client();
        $client->log('debug', 'Hello message');
    }

    public function testDefaultStreamLoggerCreation()
    {
        $client = new Client();
        $client->createDefaultStreamLogger('test', '/dev/null');

        $this->assertCount(1, $client->getLoggers());
        $this->assertArrayHasKey('test', $client->getLoggers());
    }

    public function testDefaultChromeHandler()
    {
        $client = new Client();
        $client->createDefaultChromePHPLogger('test');

        $handlers = $client->getLogger('test')->getHandlers();
        $this->assertInstanceOf('Monolog\Handler\ChromePHPHandler', $handlers[0]);

    }

    public function testGetLogger()
    {
        $client = new Client();
        $logger = $client->getLogger();
        $this->assertInstanceOf('Psr\Log\NullLogger', $logger);
    }

    public function testIsFrozen()
    {
        $client = new Client();
        $this->assertFalse($client->isFrozen());
        $client->build();
        $this->assertTrue($client->isFrozen());
    }
     **/

    public function testConvenienceMethods()
    {
        $config = $this->getDefaultConfig();
        $client = ClientBuilder::create()
            ->loadConfigurationFile($config)
            ->build();

        $root = $client->getRoot();
        $this->assertArrayHasKey('data', $root);

    }

    public function testAuthConnection()
    {
        $config = $this->getDefaultConfig();
        $client = ClientBuilder::create()
            ->loadConfigurationFile($config)
            ->build();
    }

    public function testFallbackConnection()
    {
        $client = $this->buildMultiple();
        $root = $client->getRoot();
        $this->assertArrayHasKey('data', $root);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testReadOnlyQuery()
    {
        $q = 'MERGE (n:Label) RETURN n';
        $q2 = 'CREATE (n:Label) RETURN n';
        $client = $this->build();
        $client->sendReadQuery($q);
        $client->sendReadQuery($q2);
        $client->pushToTransaction(45, $q);
        $client->pushToTransaction(46, $q2);
    }

    public function testCreateIndex()
    {
        $client = $this->build();
        $this->assertTrue($client->createIndex('Person', 'name'));
        $this->assertTrue($client->isIndexed('Person', 'name'));
        $this->assertTrue($client->dropIndex('Person', 'name'));
        $this->assertFalse($client->isIndexed('Person', 'name'));
    }

    public function testCreateMultipleIndexes()
    {
        $client = $this->build();
        $keys = array('lastname', 'firstname', 'cool');
        $client->createIndex('Person', $keys);
        $idx = $client->listIndex('Person');
        $this->assertTrue($client->isIndexed('Person', 'lastname'));
        $this->assertTrue($client->isIndexed('Person', 'firstname'));
        $this->assertTrue($client->isIndexed('Person', 'cool'));
    }

    public function testGetPathBetween()
    {
        $client = $this->build();
        $emptyQ = 'MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n';
        $client->sendCypherQuery($emptyQ);

        $loadQ = 'CREATE (n1:Link {id:1})-[:NEXT]->(n2:Link {id:2})-[:NEXT]->(n3:Link {id:3})-[:NEXT]->(n4:Link {id:4})-[:NEXT]->(n5:Link {id:5})';
        $client->sendCypherQuery($loadQ);


        $start = [
            'label' => 'Link',
            'properties' => [
                'id' => 1
            ]
        ];

        $end = [
            'label' => 'Link',
            'properties' => [
                'id' => 5
            ]
        ];
        $result = $client->getPathBetween($start, $end);
        $this->assertEquals(4, $result->getRelationshipsCount());

        $start = [
            'label' => 'Link',
            'properties' => [
                'id' => 1
            ]
        ];

        $end = [
            'label' => 'Link',
            'properties' => [
                'id' => 5
            ]
        ];
        $result = $client->getPathBetween($start, $end, null, 3);
        $this->assertEquals(0, $result->getRelationshipsCount());
    }
}