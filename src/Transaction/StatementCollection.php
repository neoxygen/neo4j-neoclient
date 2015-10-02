<?php

namespace GraphAware\Neo4j\Client\Transaction;

class StatementCollection
{
    /**
     * @var \GraphAware\Neo4j\Client\Transaction\Statement[]
     */
    protected $statements = [];

    /**
     * @var string|null
     */
    protected $tag;

    public function __construct($tag = null)
    {
        $this->tag = $tag;
    }

    /**
     * @return \GraphAware\Neo4j\Client\Transaction\Statement[]
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * @param \GraphAware\Neo4j\Client\Transaction\Statement $statement
     */
    public function add(Statement $statement)
    {
        $this->statements[] = $statement;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->statements);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->statements);
    }

    /**
     * @return null|string
     */
    public function getTag()
    {
        return $this->tag;
    }
}