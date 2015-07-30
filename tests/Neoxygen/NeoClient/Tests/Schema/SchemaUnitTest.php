<?php

namespace Neoxygen\NeoClient\Tests\Schema;

use Neoxygen\NeoClient\Schema\Index;
use Neoxygen\NeoClient\Schema\UniqueConstraint;

class SchemaUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group unit
     * @group schema
     */
    public function testIndexIsCreatedWithLabelAndName()
    {
        $index = new Index("User", "id");
        $this->assertEquals("User", $index->getLabel());
        $this->assertEquals("id", $index->getProperty());
    }

    /**
     * @group schema
     */
    public function testSchemaConstraintIsCreatedWithLabelAndName()
    {
        $constraint = new UniqueConstraint("User", "id");
        $this->assertEquals("User", $constraint->getLabel());
        $this->assertEquals("id", $constraint->getProperty());
    }
}