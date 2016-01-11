<?php

namespace GraphAware\Neo4j\Client\HttpDriver\Result;

use GraphAware\Common\Cypher\StatementInterface;
use GraphAware\Common\Result\ResultSummaryInterface;

class ResultSummary implements ResultSummaryInterface
{
    protected $statement;

    protected $updateStatistics;

    protected $notifications;

    protected $type;

    public function __construct(StatementInterface $statement)
    {
        $this->statement = $statement;
    }

    public function statement()
    {
        return $this->statement;
    }

    public function updateStatistics()
    {
        return $this->updateStatistics;
    }

    public function notifications()
    {
        return $this->notifications;
    }

    public function statementType()
    {
        return $this->type;
    }

    public function setStatistics(StatementStatistics $statistics)
    {
        $this->updateStatistics = $statistics;
    }

}