<?php

namespace GraphAware\Neo4j\Client\Tests\Unit\Transaction;

use GraphAware\Neo4j\Client\Transaction\Statement;

/**
 * @group unit
 * @group transaction
 * @group statement
 */
class StatementUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testStatementCreationWithoutParams()
    {
        $st = new Statement($this->getQuery());
        $this->assertInstanceOf(Statement::class, $st);
        $this->assertEquals($this->getQuery(), $st->getQuery());
        $this->assertInternalType('array', $st->getParameters());
        $this->assertCount(0, $st->getParameters());
        $this->assertNull($st->getTag());
        $this->assertFalse($st->hasIncludeStats());
    }

    private function getQuery()
    {
        return 'MATCH (n) RETURN count(n)';
    }
}