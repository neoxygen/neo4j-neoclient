<?php

namespace Neoxygen\NeoClient\Tests;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Neoxygen\NeoClient\Client;
use Symfony\Component\Yaml\Yaml;

class ClientTest extends NeoClientTestCase
{

    public function testContainerIsNotFrozenOnConstruct()
    {
        $client = new Client();
        $sc = $client->getServiceContainer();

        $this->assertFalse($sc->isFrozen());
    }

    public function testDefaultAttributes()
    {
        $client = new Client();
        $this->assertInternalType('array', $client->getConfiguration());
    }

    public function testAddingANewConnection()
    {
        $client = new Client();
        $client->addConnection('default', 'http', 'localhost', 7474);

        $this->assertArrayHasKey('default', $client->getConfiguration()['connections']);
        $client->addConnection('second', 'https', 'localhost', 7575);
        $this->assertArrayHasKey('second', $client->getConfiguration()['connections']);
        $this->assertCount(2, $client->getConfiguration()['connections']);

        $client->build();

        $con1 = $client->getConnection('default');
        $con2 = $client->getConnection('second');
    }

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

    public function testInvokeCommand()
    {
        $client = new Client();
        $config = $this->getDefaultConfig();
        $client->loadConfigurationFile($config);
        $client->build();
        $command = $client->invoke('simple_command');

        $this->assertInstanceOf('Neoxygen\NeoClient\Command\SimpleCommand', $command);

    }

    public function testConvenienceMethods()
    {
        $client = new Client();
        $config = $this->getDefaultConfig();
        $client->loadConfigurationFile($config);
        $client->build();

        $root = json_decode($client->getRoot(), true);
        $this->assertArrayHasKey('data', $root);

    }

    public function testAuthConnection()
    {
        $client = new Client();
        $client->loadConfigurationFile($this->getDefaultConfig());
        $client->build();
    }

    public function testFallbackConnection()
    {
        $client = $this->buildMultiple();
        $root = json_decode($client->getRoot(), true);
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
}