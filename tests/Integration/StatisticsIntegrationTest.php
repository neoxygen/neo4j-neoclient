<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Tests\Integration;
use GraphAware\Common\Result\StatementStatisticsInterface;

/**
 * Class StatisticsIntegrationTest.
 *
 * @group stats-it
 */
class StatisticsIntegrationTest extends IntegrationTestCase
{
    public function testNodesCreatedWithHttp()
    {
        $this->emptyDb();
        $result = $this->client->run('CREATE (n)', null, null, 'http');
        $summary = $result->summarize();

        $this->assertEquals(1, $summary->updateStatistics()->nodesCreated());
    }

    public function testNodesDeletedWithHttp()
    {
        $this->emptyDb();
        $this->client->run('CREATE (n)');
        $result = $this->client->run('MATCH (n) DETACH DELETE n', null, null, 'http');

        $this->assertEquals(1, $result->summarize()->updateStatistics()->nodesDeleted());
    }

    public function testRelationshipsCreatedWithHttp()
    {
        $this->emptyDb();
        $result = $this->client->run('CREATE (n)-[:REL]->(x)', null, null, 'http');

        $this->assertEquals(1, $result->summarize()->updateStatistics()->relationshipsCreated());
    }

    public function testRelationshipsDeletedWithHttp()
    {
        $this->emptyDb();
        $this->client->run('CREATE (n)-[:REL]->(x)');
        $result = $this->client->run('MATCH (n) DETACH DELETE n', null, null, 'http');

        $this->assertEquals(1, $result->summarize()->updateStatistics()->relationshipsDeleted());
    }

    /**
     * @group bolt-stats
     */
    public function testNodesCreatedWithBolt()
    {
        $this->emptyDb();
        $result = $this->client->run('MATCH (n) RETURN count(n)', [], null, 'bolt');
        $this->assertInstanceOf(StatementStatisticsInterface::class, $result->summarize()->updateStatistics());

        $tx = $this->client->transaction('bolt');
        $result = $tx->run('MATCH (n) RETURN count(n)');
        $tx->commit();
        $this->assertInstanceOf(StatementStatisticsInterface::class, $result->summarize()->updateStatistics());
    }
}
