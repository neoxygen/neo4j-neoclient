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

    private $responseFormatter;

    public function __construct($conn = null, NeoClientCoreExtension $extension, $responseFormatter)
    {
        $this->conn = $conn;
        $this->client = $extension;
        $this->responseFormatter = $responseFormatter;
        $response = $this->handleResponse($this->client->openTransaction($this->conn));
        $this->commitUrl = $response->getResponse()['commit'];
        $this->parseTransactionId();
        $this->active = true;

        return $this;
    }

    public function pushQuery($query, array $parameters = array(), array $resultDataContents = array())
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->pushToTransaction($this->transactionId, $query, $parameters, $this->conn, $resultDataContents));

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

    private function parseTransactionId()
    {
        $expl = explode('/', $this->commitUrl);
        $this->transactionId = (int) $expl[6];
    }

    private function checkIfOpened()
    {
        if (!$this->isActive()) {
            throw new \RuntimeException('The transaction has not been opened or is closed');
        }
    }

    private function checkResultErrors(array $response)
    {
        if (!empty($response['errors'])) {
            throw new \Exception(sprintf('Transaction Error : %s', $response['errors'][0]['message']));
        }
    }

    private function handleResponse($httpResponse)
    {
        if (!$this->responseFormatter->isNew()){
            $this->responseFormatter->reset();
        }
        $response = $this->responseFormatter->format($httpResponse);

        if ($response->hasErrors()){
            throw new Neo4jException($response->getErrors()['message']);
        }

        return $response;
    }
}
