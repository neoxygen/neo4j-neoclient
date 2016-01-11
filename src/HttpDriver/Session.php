<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\HttpDriver;

use GraphAware\Common\Cypher\Statement;
use GraphAware\Common\Driver\SessionInterface;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Neo4j\Client\Formatter\ResponseFormatter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class Session implements SessionInterface
{
    protected $uri;

    protected $httpClient;

    protected $responseFormatter;

    public function __construct($uri, Client $httpClient)
    {
        $this->uri = $uri;
        $this->httpClient = $httpClient;
        $this->responseFormatter = new ResponseFormatter();
    }

    public function run($statement, $parameters, $tag = null)
    {
        $st = Statement::create($statement, $parameters, $tag);
        $request = $this->prepareRequest($statement, $parameters);

        try {
            $response = $this->httpClient->send($request);
            $results = $this->responseFormatter->format(json_decode($response->getBody(), true), array($st));

            return $results[0];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $body = json_decode($e->getResponse()->getBody(), true);
                if (!isset($body['code'])) {
                    throw $e;
                }
                $msg = sprintf('Neo4j Exception with code "%s" and message "%s"', $body['errors'][0]['code'], $body['errors'][0]['message']);
                $exception = new Neo4jException($msg);
                $exception->setNeo4jStatusCode($body['errors'][0]['code']);

                throw $exception;
            }

            throw $e;
        }
    }

    public function close()
    {
        //
    }

    public function prepareRequest($statement, $parameters)
    {
        $info = parse_url($this->uri);
        $host = sprintf('%s://%s:%d/db/data/transaction/commit', $info['scheme'], $info['host'], $info['port']);
        $statements = [
            'statements' => []
        ];
        $st = [
            'statement' => $statement,
            'resultDataContents' => ['REST'],
            'includeStats' => true
        ];

        if (is_array($parameters) && !empty($parameters)) {
            $st['parameters'] = $parameters;
        }

        $statements['statements'][] = $st;

        $options = [];
        $options['headers'] = [
            [
                'X-Stream' => true,
                'Content-Type' => 'application/json'
            ]
        ];

        if (isset($info['user']) && isset($info['pass'])) {
            $options['auth'] = [$info['user'], $info['pass']];
        }

        $options['json'] = $st;

        $request = new Request("POST", sprintf('%s/db/data/transaction/commit', $this->uri), $options['headers'], json_encode($statements));

        echo (string) $request->getBody();

        return $request;
    }

}