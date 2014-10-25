<?php

namespace Neoxygen\NeoClient\Transaction;

use Neoxygen\NeoClient\Client;

class Transaction
{
    private $client;

    private $active;

    private $conn;

    private $commitUrl;

    private $transactionId;

    private $results = [];

    public function __construct($conn = null, Client $client)
    {
        $this->conn = $conn;
        $this->client = $client;
        $response = $this->handleResponse($this->client->openTransaction($this->conn));
        $this->commitUrl = $response['commit'];
        $this->parseTransactionId();
        $this->active = true;

        return $this;
    }

    public function pushQuery($query, array $parameters = array(), array $resultDataContents = array())
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->pushToTransaction($this->transactionId, $query, $parameters, $this->conn, $resultDataContents));
        $this->checkResultErrors($response);
        $this->results[] = $response['results'];

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

    private function handleResponse($response)
    {
        if (!is_array($response)){
            $response = json_decode($response, true);
        }

        return $response;
    }
}
