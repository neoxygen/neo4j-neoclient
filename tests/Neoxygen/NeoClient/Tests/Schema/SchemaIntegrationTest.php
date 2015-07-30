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
        $indexes = $this->client->getSchemaIndexes();

        $this->assertThat(
          $index,
          new SchemaContainsIndexConstraint($indexes)
        );
    }

    /**
     * @group schema
     * @group integration
     */
    public function testMultipleIndexesAreReturned()
    {
        $this->client->createIndex("SchemaNode", "id");
        $this->client->createIndex("SchemaNode", "login");
        $indexes = $this->client->getSchemaIndexes();

        $this->assertCount(2, $indexes);
    }
}