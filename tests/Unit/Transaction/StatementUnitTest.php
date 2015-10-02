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

    public function testStatementWithFullSignature()
    {
        $st = new Statement($this->getQuery(), ['id' => 1], 'example.tag', true);
        $this->assertCount(1, $st->getParameters());
        $this->assertNotNull($st->getTag());
        $this->assertEquals(1, $st->getParameters()['id']);
        $this->assertTrue($st->hasIncludeStats());
        $this->assertEquals('example.tag', $st->getTag());
    }

    public function testStatementStaticCreation()
    {
        $st = Statement::create($this->getQuery(), ['ID' => 2], 'static.tag');
        $this->assertInstanceOf(Statement::class, $st);
        $this->assertEquals('static.tag', $st->getTag());
        $this->assertEquals(2, $st->getParameters()['ID']);
        $this->assertFalse($st->hasIncludeStats());
    }

    public function testStatementSkippingParamsAndTag()
    {
        $st = Statement::create($this->getQuery(), [], null, true);
        $this->assertTrue($st->hasIncludeStats());
    }

    private function getQuery()
    {
        return 'MATCH (n) RETURN count(n)';
    }
}