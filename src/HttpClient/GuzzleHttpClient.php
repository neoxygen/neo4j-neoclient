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
    Neoxygen\NeoClient\Event\LoggingEvent,
    Neoxygen\NeoClient\Exception\HttpException,
    Neoxygen\NeoClient\Formatter\ResponseFormatterInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    private $eventDispatcher;

    private $connectionManager;

    private $slavesUsed = [];

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConnectionManager $connectionManager)
    {
        $this->client = new Client();
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
            $this->logMessage('debug',sprintf('Sending query to the "%s" connection', $conn->getAlias()));
            $response = $this->client->send($httpRequest);
            $this->slavesUsed = [];

            return $this->getResponse($response);
        } catch (RequestException $e) {
            if ($slaveConn === false) {
            }
                if ($this->connectionManager->hasFallbackConnection($conn->getAlias())) {
                    $this->logMessage('alert', sprintf('Connection "%s" unreachable, using fallback connection', $conn->getAlias()));
                    $fallback = $this->connectionManager->getFallbackConnection($conn->getAlias());

                    return $this->send($method, $path, $body, $fallback->getAlias(), $queryString);
            } elseif ($slaveConn) {
                    $this->slavesUsed[] = $connectionAlias;
                    if ($this->connectionManager->hasNextSlave($this->slavesUsed)) {
                            $nextSlave = $this->connectionManager->getNextSlave($this->slavesUsed);
                            $this->logMessage(
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
                    $this->logMessage('emergency', $message);
                    throw new HttpException($message, $e->getCode());
                }
        }

    }

    private function getResponse($response)
    {
        $this->logMessage(
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

    private function logMessage($level = 'DEBUG', $message, $context = null)
    {
        $event = new LoggingEvent($level, $message, $context);
        $this->eventDispatcher->dispatch(NeoClientEvents::NEO_LOG_MESSAGE, $event);
    }
}
