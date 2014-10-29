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
use GuzzleHttp\Exception\RequestException;
use Neoxygen\NeoClient\Connection\ConnectionManager;
use Neoxygen\NeoClient\Request\RequestInterface,
    Neoxygen\NeoClient\NeoClientEvents,
    Neoxygen\NeoClient\Event\HttpClientPreSendRequestEvent,
    Neoxygen\NeoClient\Exception\HttpException,
    Neoxygen\NeoClient\Formatter\ResponseFormatterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    private $logger;

    private $eventDispatcher;

    private $connectionManager;

    private $slavesUsed = [];

    public function __construct(
        LoggerInterface $logger = null,
        EventDispatcherInterface $eventDispatcher = null,
        ConnectionManager $connectionManager)
    {
        $this->client = new Client();
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->connectionManager = $connectionManager;
    }

    public function send($method, $path, $body = null, $connectionAlias = null, $queryString = null, $slaveConn = false)
    {
        $conn = $this->connectionManager->getConnection($connectionAlias);
        $url = $conn->getBaseUrl() . $path;
        $defaults = array(
            'body' => $body,
            'timeout' => 1
        );
        if ($queryString) {
            $defaults['query'] = $queryString;
        }
        $httpRequest = $this->client->createRequest($method, $url, $defaults);
        if ($conn->isAuth()) {
            $httpRequest->setHeader('Authorization', 'Basic '.base64_encode($conn->getAuthUser().':'.$conn->getAuthPassword()));
        }

        try {
            $response = $this->client->send($httpRequest);
            $this->slavesUsed = [];

            return $this->getResponse($response);
        } catch (RequestException $e) {
            if ($slaveConn === false) {
            }
                if ($this->connectionManager->hasFallbackConnection($conn->getAlias())) {
                    $this->logger->log('alert', sprintf('Connection "%s" unreachable, using fallback connection', $conn->getAlias()));
                    $fallback = $this->connectionManager->getFallbackConnection($conn->getAlias());

                    return $this->send($method, $path, $body, $fallback->getAlias(), $queryString);
            } elseif ($slaveConn) {
                    $this->slavesUsed[] = $connectionAlias;
                    if ($this->connectionManager->hasNextSlave($this->slavesUsed)) {
                            $nextSlave = $this->connectionManager->getNextSlave($this->slavesUsed);
                            $this->logger->log(
                                'alert',
                                sprintf(
                                    'Slave Connection "%s" unreacheable, auto fallback to slave "%s"',
                                    $conn->getAlias(),
                                    $nextSlave)
                                );

                            return $this->send($method, $path, $body, $nextSlave, $queryString, $slaveConn);
                    }
                } else {
                    $message = (string) $e->getRequest() ."\n";
                    if ($e->hasResponse()) {
                        $message .= (string) $e->getResponse() ."\n";
                    }
                    $this->logger->log('emergency', $message);
                    throw new HttpException($message, $e->getCode());
                }
        }

    }

    private function getResponse($response)
    {
        $this->logger->log(
            'debug',
            sprintf('Http Response received'),
            array('response' => (string) $response->getBody())
        );

        if ($response->getBody()) {
                $resp = (string) $response->getBody();
                $decoded = \GuzzleHttp\json_decode($resp, true);

                return $decoded;
            }

        return null;
    }

    private function dispatchPreRequest(RequestInterface $request)
    {
        $event = new HttpClientPreSendRequestEvent($request);
        $this->eventDispatcher->dispatch(NeoClientEvents::NEO_HTTP_PRE_REQUEST_SEND, $event);
    }
}
