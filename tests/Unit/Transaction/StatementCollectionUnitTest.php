<?php

namespace GraphAware\Neo4j\Client\Tests\Unit\Transaction;

use GraphAware\Neo4j\Client\Transaction\Statement;
use GraphAware\Neo4j\Client\Transaction\StatementCollection;

/**
 * @group unit
 * @group transaction
 * @group statement
 */
class StatementCollectionUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionCreation()
    {
        $coll = new StatementCollection();
        $this->assertInstanceOf(StatementCollection::class, $coll);
        $this->assertCount(0, $coll->getStatements());
        $this->assertNull($coll->getTag());
        $this->assertTrue($coll->isEmpty());
    }

    public function testCollectionWithStatements()
    {
        $coll = new StatementCollection();
        $coll->add($this->getQuery());
        $this->assertCount(1, $coll->getStatements());
        $this->assertFalse($coll->isEmpty());
        $this->assertEquals(1, $coll->getCount());
    }

    public function testCollectionWithTag()
    {
        $coll = new StatementCollection('coll');
        $this->assertEquals('coll', $coll->getTag());
    }

    private function getQuery()
    {
        return Statement::create('MATCH (n) RETURN count(n)');
    }
}