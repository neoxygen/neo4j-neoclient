<?php

namespace Neoxygen\NeoClient\Transaction;

use Neoxygen\NeoClient\Extension\NeoClientCoreExtension;
use Neoxygen\NeoClient\Exception\Neo4jException;

class Transaction
{
    private $client;

    private $active;

    private $conn;

    private $commitUrl;

    private $transactionId;

    private $results = [];

    public function __construct($conn = null, NeoClientCoreExtension $extension)
    {
        $this->conn = $conn;
        $this->client = $extension;
        $response = $this->handleResponse($this->client->openTransaction($this->conn));
        $this->commitUrl = $response->getBody()['commit'];
        $this->parseTransactionId();
        $this->active = true;

        return $this;
    }

    public function pushQuery($query, array $parameters = array())
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->pushToTransaction($this->transactionId, $query, $parameters, $this->conn));
        $this->results[] = $response->getResult();

        return $this;
    }

    public function pushMultiple(array $statements)
    {
        $this->checkIfOpened();
        $this->client->pushMultipleToTransaction($this->getTransactionId(), $statements, $this->conn);

        return $this;
    }

    public function commit()
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->commitTransaction($this->transactionId));
        $this->active = false;

        return $response;
    }

    public function rollback()
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->rollBackTransaction($this->transactionId));
        $this->active = false;

        return $response;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getLastResult()
    {
        $last = end($this->results);
        reset($this->results);

        return $last;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    private function parseTransactionId()
    {
        $expl = explode('/', $this->commitUrl);
        $this->transactionId = (int) $expl[6];
    }

    private function checkIfOpened()
    {
        if (!$this->isActive()) {
            throw new Neo4jException('The transaction has not been opened or is closed');
        }
    }

    private function handleResponse($httpResponse)
    {
        return $this->client->handleHttpResponse($httpResponse);
    }
}
