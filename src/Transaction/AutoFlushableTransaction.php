<?php

namespace GraphAware\Neo4j\Client\Transaction;

class AutoFlushableTransaction extends AbstractTransaction
{
    /**
     * @var int
     */
    protected $treshold;

    /**
     * @var \GraphAware\Neo4j\Client\Transaction\StatementCollection
     */
    protected $statementsCollection;

    /**
     * @param string $treshold
     * @param null|string $mode
     * @param null|string $tag
     */
    public function __construct($treshold, $mode = TransactionInterface::TRANSACTION_WRITE, $tag = null)
    {
        $this->treshold = (int) $treshold;
        $this->statementsCollection = new StatementCollection();
        parent::__construct($mode, $tag);
    }

    public function commit()
    {

    }

    public function rollback()
    {

    }

    /**
     * @param \GraphAware\Neo4j\Client\Transaction\Statement $statement
     */
    public function push(Statement $statement)
    {
        $this->statementsCollection->add($statement);
        if ($this->statementsCollection->getCount() >= $this->treshold) {
            $this->commit();
        }
    }

    /**
     * @param string $query
     * @param array $parameters
     */
    public function pushQuery($query, array $parameters = [])
    {
        $this->push(Statement::create($query, $parameters));
    }

    /**
     * @return int
     */
    public function getTreshold()
    {
        return $this->treshold;
    }

    /**
     * @return \GraphAware\Neo4j\Client\Transaction\Statement[]
     */
    public function getStatements()
    {
        return $this->statementsCollection->getStatements();
    }
}