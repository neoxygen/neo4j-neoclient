<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Transaction;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Transaction\TransactionInterface;
use GraphAware\Neo4j\Client\Connection\Connection;
use GraphAware\Neo4j\Client\Stack;

class Transaction implements TransactionInterface
{
    const OPENED = 'OPEN';

    const COMMITTED = 'COMMITED';

    const ROLLED_BACK = 'ROLLED_BACK';

    /**
     * @var \GraphAware\Neo4j\Client\Connection\Connection
     */
    protected $connection;

    /**
     * @var null|string
     */
    protected $state;

    /**
     * @var array()
     */
    protected $queue = [];

    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var array
     */
    protected $taggedResults = [];

    /**
     * @param \GraphAware\Neo4j\Client\Connection\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function push($statement, array $parameters = array(), $tag = null)
    {
        $this->queue[] = Statement::create($statement, $parameters, $tag);
    }

    public function pushStack(Stack $stack)
    {
        $this->queue[] = $stack;
    }

    public function begin()
    {
        if ($this->state === self::ROLLED_BACK || $this->state === self::COMMITTED) {
            throw new \RuntimeException(sprintf('Cannot begin a transaction when state is "%s"', $this->state)); // @todo change to TransactionException
        }
    }

    public function isOpen()
    {
        return self::OPENED === $this->state;
    }

    public function isCommited()
    {
        return self::COMMITTED === $this->state;
    }

    public function isRolledBack()
    {
        return self::ROLLED_BACK === $this->state;
    }

    public function status()
    {
        return $this->state;
    }

    public function commit()
    {
        return $this->connection->runMixed($this->queue);
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}