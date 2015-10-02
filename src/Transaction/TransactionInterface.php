<?php

namespace GraphAware\Neo4j\Client\Transaction;

interface TransactionInterface
{
    const TRANSACTION_WRITE = 'TRANSACTION_WRITE';

    const TRANSACTION_READ = 'TRANSACTION_READ';

    public function commit();

    public function rollback();

    public function isCommitted();

    public function isRolledBack();

    public function isRunning();
}