<?php

namespace Neoxygen\NeoClient\Tests\Schema;

use Neoxygen\NeoClient\ClientBuilder;
use Neoxygen\NeoClient\Schema\Index;
use Neoxygen\NeoClient\Tests\Helper\SchemaContainsIndexConstraint;

class SchemaIntegrationTest extends GraphUnitTestCase
{
    /**
     * @return \Neoxygen\NeoClient\Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = $this->getConnection();
        $this->resetDatabase();
    }

    /**
     * @group schema
     * @group integration
     */
    public function testIndexObjectIsReturnedWhenCreatingIndex()
    {
        $index = $this->client->createSchemaIndex("SchemaNode", "id");
        $this->assertInstanceOf('Neoxygen\NeoClient\Schema\Index', $index);
    }

    /**
     * @group schema
     * @group integration
     */
    public function testSchemaIndexesAreReturned()
    {
        $index = $this->client->createSchemaIndex("SchemaNode", "id");
        $this->assertIndexIsLive($index);
    }

    /**
     * @group schema
     * @group integration
     */
    public function testMultipleIndexesAreReturned()
    {
        $this->client->createSchemaIndex("SchemaNode", "id");
        $this->client->createSchemaIndex("SchemaNode", "login");
        $indexes = $this->client->getSchemaIndexes();

        $this->assertCount(2, $indexes);
    }

    /**
     * @group schema
     * @group integration
     */
    public function testIndexIsDropped()
    {
        $index = $this->client->createSchemaIndex("SchemaNode", "id");
        $this->client->dropSchemaIndex($index);

        $this->assertCount(0, $this->client->getSchemaIndexes());

    }

    private function assertIndexIsLive(Index $index)
    {
        $indexes = $this->client->getSchemaIndexes();

        $this->assertThat(
          $index,
          new SchemaContainsIndexConstraint($indexes)
        );
    }
}