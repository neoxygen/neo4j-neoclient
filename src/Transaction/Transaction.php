<?php

namespace Neoxygen\NeoClient\Transaction;

use Neoxygen\NeoClient\Exception\HttpException;
use Neoxygen\NeoClient\Extension\NeoClientCoreExtension;
use Neoxygen\NeoClient\Exception\Neo4jException;

class Transaction
{
    /**
     * @var \Neoxygen\NeoClient\Extension\NeoClientCoreExtension|\Neoxygen\NeoClient\Extension\AbstractExtension
     */
    private $client;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var string|null
     */
    private $conn;

    /**
     * @var string
     */
    private $commitUrl;

    /**
     * @var int
     */
    private $transactionId;

    /**
     * @var string
     */
    private $queryMode;

    /**
     * @var \Neoxygen\NeoClient\Formatter\Result[]
     */
    private $results = [];

    /**
     * @param null                                                 $conn
     * @param \Neoxygen\NeoClient\Extension\NeoClientCoreExtension $extension
     */
    public function __construct($conn = null, NeoClientCoreExtension $extension, $queryMode)
    {
        $this->conn = $conn;
        $this->queryMode = $queryMode;
        $this->client = $extension;
        $response = $this->handleResponse($this->client->openTransaction($this->conn, $this->queryMode));
        $this->commitUrl = $response->getBody()['commit'];
        $this->parseTransactionId();
        $this->active = true;

        return $this;
    }

    /**
     * @param $query
     * @param array $parameters
     *
     * @return \Neoxygen\NeoClient\Formatter\Result
     *
     * @throws \Neoxygen\NeoClient\Exception\Neo4jException
     */
    public function pushQuery($query, array $parameters = array())
    {
        $this->checkIfOpened();
        try {
            $response = $this->handleResponse($this->client->pushToTransaction($this->transactionId, $query, $parameters, $this->conn));
            $result = $response->getResult();
            $this->results[] = $result;

            return $result;
        } catch(HttpException $e) {
            $this->rollback();
            throw $e;
        }

    }

    /**
     * @param array $statements
     *
     * @return \Neoxygen\NeoClient\Formatter\Result|\GraphAware\NeoClient\Formatter\Results[]
     *
     * @throws \Neoxygen\NeoClient\Exception\Neo4jException
     */
    public function pushMultiple(array $statements)
    {
        $this->checkIfOpened();
        try {
            $httpResponse = $this->client->pushMultipleToTransaction($this->transactionId, $statements);

            $response = $this->handleResponse($httpResponse);

            if ($this->client->newFormattingService) {
                return $response->getResults();
            }

            return $response->getResult();
        } catch(HttpException $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @return array|\Neoxygen\NeoClient\Formatter\Response|string
     *
     * @throws \Neoxygen\NeoClient\Exception\Neo4jException
     */
    public function commit()
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->commitTransaction($this->transactionId, null, array(), $this->conn, $this->queryMode));
        $this->active = false;

        return $response;
    }

    /**
     * @return array|\Neoxygen\NeoClient\Formatter\Response|string
     *
     * @throws \Neoxygen\NeoClient\Exception\Neo4jException
     */
    public function rollback()
    {
        $this->checkIfOpened();
        $response = $this->handleResponse($this->client->rollBackTransaction($this->transactionId));
        $this->active = false;

        return $response;
    }

    /**
     * @return \Neoxygen\NeoClient\Formatter\Result[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return mixed
     */
    public function getLastResult()
    {
        $last = end($this->results);
        reset($this->results);

        return $last;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     *
     */
    private function parseTransactionId()
    {
        $expl = explode('/', $this->commitUrl);
        $this->transactionId = (int) $expl[6];
    }

    /**
     * @throws \Neoxygen\NeoClient\Exception\Neo4jException
     */
    private function checkIfOpened()
    {
        if (!$this->isActive()) {
            throw new Neo4jException('The transaction has not been opened or is closed');
        }
    }

    /**
     * @param $httpResponse
     *
     * @return array|\Neoxygen\NeoClient\Formatter\Response|string|\GraphAware\NeoClient\Formatter\Response
     */
    private function handleResponse($response)
    {
        if ($this->client->newFormatModeEnabled === true) {
            return $response;
        }

        return $this->client->handleHttpResponse($response);
    }
}
