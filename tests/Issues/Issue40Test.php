<?php

namespace GraphAware\Neo4j\Client\Tests\Issues;

use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;

/**
 * Class Issue40Test
 * @package GraphAware\Neo4j\Client\Tests\Issues
 *
 * @group issues
 */
class Issue40Test extends IntegrationTestCase
{
    public function testIssue()
    {
        $this->emptyDb();
        $this->client->run('CREATE (:BRIEF {id: 123})');

        $query = 'MATCH (s:BRIEF {id:{brief_id}})
    CREATE (n:BRIEFNOTECARD)
    SET n += {data}
    CREATE (n)-[:CARD_OF {order:0}]->(s)
    RETURN n';

        $parameters = [
            'brief_id' => 123,
            'data' => [
                'key_1' => 'test',
                'key_2' => 'other'
            ]
        ];

        $this->client->run($query, $parameters);
        $this->assertGraphExist('(n:BRIEF {id: 123})<-[:CARD_OF {order:0}]-(:BRIEFNOTECARD)');
    }

    private function assertGraphExist($pattern)
    {
        $q = sprintf('MATCH %s RETURN *', $pattern);
        $result = $this->client->run($q);

        $this->assertTrue(0 !== $result->size());
    }
}