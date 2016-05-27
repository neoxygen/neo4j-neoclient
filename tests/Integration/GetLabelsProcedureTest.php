<?php

namespace GraphAware\Neo4j\Client\Tests;

use GraphAware\Neo4j\Client\Tests\Integration\IntegrationTestCase;
use GraphAware\Neo4j\Client\Schema\Label;

/**
 * Class GetLabelsProcedureTest
 * @package GraphAware\Neo4j\Client\Tests
 *
 * @group procedure
 */
class GetLabelsProcedureTest extends IntegrationTestCase
{
    public function testCanGetLabels()
    {
        $this->emptyDb();
        $this->client->run("CREATE (:Label1), (:Label2), (:Label3)");
        $result = $this->client->getLabels();
        $this->assertCount(3, $result);
        foreach ($result as $label) {
            $this->assertInstanceOf(Label::class, $label);
        }
    }
}