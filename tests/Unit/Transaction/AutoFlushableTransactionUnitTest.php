<?php

namespace GraphAware\Neo4j\Client\Tests\Unit\Transaction;

use GraphAware\Neo4j\Client\Transaction\AutoFlushableTransaction;
use GraphAware\Neo4j\Client\Transaction\TransactionInterface;

/**
 * @group unit
 * @group transaction
 */
class AutoFlushableTransactionUnitTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $tx = new AutoFlushableTransaction(10);
        $this->assertInstanceOf(AutoFlushableTransaction::class, $tx);
        $this->assertInstanceOf(TransactionInterface::class, $tx);
        $this->assertEquals(10, $tx->getTreshold());
        $this->assertNull($tx->getTag());
        $this->assertCount(0, $tx->getStatements());
        $this->assertFalse($tx->isRunning());
        $this->assertFalse($tx->isRolledBack());
        $this->assertFalse($tx->isCommitted());
    }

    public function testPushToTransaction()
    {
        $tx = new AutoFlushableTransaction(10);
        $tx->pushQuery('MATCH (n) RETURN n');
        $this->assertCount(1, $tx->getStatements());
        $this->assertFalse($tx->isCommitted());
    }
}