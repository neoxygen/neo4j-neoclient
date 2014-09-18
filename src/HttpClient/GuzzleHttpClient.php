<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Neoxygen\NeoClient\HttpClient;

use GuzzleHttp\Client;
use Neoxygen\NeoClient\Connection\ConnectionManager;
use Neoxygen\NeoClient\Request\RequestInterface,
    Neoxygen\NeoClient\NeoClientEvents,
    Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    private $responseFormat;

    private $logger;

    private $eventDispatcher;

    private $connectionManager;

    public function __construct(
        $responseFormat = null,
        LoggerInterface $logger = null,
        EventDispatcherInterface $eventDispatcher = null,
        ConnectionManager $connectionManager)
    {
        $this->client = new Client();
        $this->responseFormat = null === $responseFormat ? 'json' : $responseFormat;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->connectionManager = $connectionManager;
    }

    public function send($method, $path, $body = null, $connectionAlias = null, $queryString = null)
    {

        $conn = $this->connectionManager->getConnection($connectionAlias);
        $url = $conn->getBaseUrl() . $path;
        $defaults = array(
            'body' => $body
        );
        if ($queryString) {
            $defaults['query'] = $queryString;
        }
        $httpRequest = $this->client->createRequest($method, $url, $defaults);
        if ($conn->isAuth()) {
            $httpRequest->setHeader('Authorization', 'Basic '.base64_encode($conn->getAuthUser().':'.$conn->getAuthPassword()));
        }

        $response = $this->client->send($httpRequest);

        return $this->getResponse($response);
    }

    public function sendRequest(RequestInterface $request)
    {
        $body = ($request->getBody()) ? $request->getBody() : null;
        $defaults = array(
            'body' => $body
        );

        $httpRequest = $this->client->createRequest($request->getMethod(), $request->getUrl(), $defaults);
        $httpRequest->setHeaders($request->getHeaders());

        $this->logger->log(
            'debug',
            sprintf('Sending http request to %s', $request->getUrl()),
            array('body' => (string) $request->getBody())
        );
        $this->dispatchPreRequest($request);

        $response = $this->client->send($httpRequest);

        return $this->getResponse($response);

    }

    private function getResponse($response)
    {
        $this->logger->log(
            'debug',
            sprintf('Http Response received'),
            array('response' => (string) $response->getBody())
        );

        if ($response->getBody()) {
            if ($this->responseFormat === 'json') {
                return (string) $response->getBody();
            }

            return $response->json();
        }

        return null;
    }

    private function dispatchPreRequest(RequestInterface $request)
    {
        $event = new HttpClientPreSendRequestEvent($request);
        $this->eventDispatcher->dispatch(NeoClientEvents::NEO_HTTP_PRE_REQUEST_SEND, $event);
    }
}
