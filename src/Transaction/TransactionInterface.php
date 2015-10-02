<?php

namespace GraphAware\Neo4j\Client\Transaction;

interface TransactionInterface
{
    public function commit();

    public function rollback();

    public function isCommitted();

    public function isRolledBack();

    public function isRunning();
}