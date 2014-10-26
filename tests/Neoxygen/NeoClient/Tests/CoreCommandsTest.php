<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\ClientBuilder;

class CoreCommandsTest extends NeoClientTestCase
{
    public function testPingCommand()
    {
        $config = $this->getDefaultConfig();
        $client = ClientBuilder::create()
            ->loadConfigurationFile($config)
            ->build();
        $this->assertNull($client->ping());
    }

    public function testGetLabelsCommand()
    {
        $config = $this->getDefaultConfig();
        $client = ClientBuilder::create()
            ->loadConfigurationFile($config)
            ->build();
        $con = $client->getConnection();

        $q = 'MERGE (n:TestLabel) RETURN n';
        $client->sendCypherQuery($q);
        $labels = $client->getLabels();

        $this->assertContains('TestLabel', $labels);
    }

    public function testGetVersionCommand()
    {
        $sc = $this->build();
        $this->assertContains('2.1', $sc->getVersion());
    }

    public function testOpenTransactionCommand()
    {
        $sc = $this->build();
        $tx = $sc->openTransaction();

        $this->assertArrayHasKey('commit', $tx);
        $this->assertArrayHasKey('transaction', $tx);
    }

    public function testRollBackTransaction()
    {
        $sc = $this->build();
        $transaction = $sc->openTransaction();
        $expl = explode('/', $transaction['commit']);
        $tx_id = $expl[count($expl)-2];

        $response = $sc->rollbackTransaction($tx_id);

        $this->assertEmpty($response['errors']);
    }

    public function testPushToTransaction()
    {
        $sc = $this->build();
        $transaction = $sc->openTransaction();
        $expl = explode('/', $transaction['commit']);
        $tx_id = $expl[count($expl)-2];

        $q = 'MATCH (n) RETURN count(n)';
        $sc->pushToTransaction($tx_id, $q);
        $response = $sc->getLastResponse();
        $this->assertTrue($response->containsResults());
    }

    public function testSendCypher()
    {
        $client = $this->build();
        $q = 'MATCH (n) RETURN n';
        $resultFormat = array('row', 'graph');
        $response = $client->sendCypherQuery($q, array(), null, $resultFormat);
        $result = $client->getLastResponse()->getResponse();
        $this->assertArrayHasKey('graph', $result['results'][0]['data'][0]);
    }
}

