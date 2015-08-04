<?php

namespace Neoxygen\NeoClient\Tests\Schema;

use Neoxygen\NeoClient\ClientBuilder;
use Neoxygen\NeoClient\Schema\Index;
use Neoxygen\NeoClient\Schema\UniqueConstraint;
use Neoxygen\NeoClient\Tests\Helper\SchemaContainsIndexConstraint;

class SchemaIntegrationTest extends GraphUnitTestCase
{
    /**
     * @var \Neoxygen\NeoClient\Client
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

    /**
     * @group schema
     * @group integration
     */
    public function testUniqueConstraintIsCreated()
    {
        $constraint = $this->client->createSchemaUniqueConstraint("UniqueNode", "id");
        $this->assertInstanceOf('Neoxygen\NeoClient\Schema\UniqueConstraint', $constraint);
        $this->assertUniqueConstraintIsLive($constraint);

    }

    /**
     * @group schema
     * @group integration
     */
    public function testDropUniqueConstraint()
    {
        $constraint = $this->client->createSchemaUniqueConstraint("UniqueNode", "id");
        $this->client->dropSchemaUniqueConstraint($constraint);

        $this->assertCount(0, $this->client->getSchemaUniqueConstraints());
    }

    /**
     * @group schema
     * @group integration
     */
    public function testMultipleConstraintsAreReturned()
    {
        $this->client->createSchemaUniqueConstraint("UniqueNode", "id");
        $this->client->createSchemaUniqueConstraint("UniqueNode", "login");
        $this->assertCount(2, $this->client->getSchemaUniqueConstraints());
    }

    /**
     * @group schema
     * @group integration
     * @group legacy
     */
    public function testLegacyUniqueConstraints()
    {
        $this->client->createUniqueConstraint('User', 'id');
        $constraints = $this->client->getUniqueConstraints();
        $this->assertArrayHasKey('User', $constraints->getBody());
    }

    /**
     * @group legacy
     */
    public function tesLegacyUniqueConstraintsWithNewFormattingService()
    {
        $client = ClientBuilder::create()
          ->addConnection('default', 'http', 'localhost', 7474, true, 'neo4j', 'veryCoolMax')
          ->setAutoFormatResponse(true)
          ->enableNewFormattingService()
          ->build();
        $constraints = $client->getUniqueConstraints();
        $this->assertArrayHasKey('User', $constraints->getBody());
    }

    /**
     * @group schema
     */
    public function testListIndexesWithNewFormattingService()
    {
        $this->assertInternalType('array', $this->getConnection()->listIndexes()->getBody());
    }

    private function assertUniqueConstraintIsLive(UniqueConstraint $index)
    {
        $indexes = $this->client->getSchemaUniqueConstraints();

        $this->assertThat(
          $index,
          new SchemaContainsIndexConstraint($indexes)
        );
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