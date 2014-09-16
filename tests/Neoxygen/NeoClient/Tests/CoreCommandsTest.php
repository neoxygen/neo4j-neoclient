<?php

namespace Neoxygen\NeoClient\Tests;

use Neoxygen\NeoClient\Client;

class CoreCommandsTest extends NeoClientTestCase
{
    public function testPingCommand()
    {
        $client = new Client();
        $client->loadConfigurationFile($this->getDefaultConfig());
        $client->build();
        $this->assertNull($client->ping());
    }

    public function testGetLabelsCommand()
    {
        $client = new Client();
        $client->loadConfigurationFile($this->getDefaultConfig());
        $client->build();
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
        $tx = json_decode($sc->openTransaction(), true);

        $this->assertArrayHasKey('commit', $tx);
        $this->assertArrayHasKey('transaction', $tx);
    }

    public function testRollBackTransaction()
    {
        $sc = $this->build();
        $transaction = json_decode($sc->openTransaction(), true);
        $expl = explode('/', $transaction['commit']);
        $tx_id = $expl[count($expl)-2];

        $rollback = json_decode($sc->rollbackTransaction($tx_id), true);

        $this->assertArrayHasKey('results', $rollback);
        $this->assertEmpty($rollback['errors']);
    }

    public function testPushToTransaction()
    {
        $sc = $this->build();
        $transaction = json_decode($sc->openTransaction(), true);
        $expl = explode('/', $transaction['commit']);
        $tx_id = $expl[count($expl)-2];

        $q = 'MATCH (n) RETURN count(n)';
        $push = json_decode($sc->pushToTransaction($tx_id, $q), true);

        $this->assertEmpty($push['errors']);
        $this->assertNotEmpty($push['results']);
    }
}