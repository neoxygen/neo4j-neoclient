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
use Neoxygen\NeoClient\Request\RequestInterface;
use Psr\Log\LoggerInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    private $responseFormat;

    private $logger;

    public function __construct($responseFormat = 'json', LoggerInterface $logger)
    {
        $this->client = new Client();
        $this->responseFormat = $responseFormat;
        $this->logger = $logger;
    }

    public function send($method, $url, $body = null, array $headers = array())
    {
        $request = $this->client->createRequest($method, $url, array('body' => $body));

        if (!empty($headers)) {
            $request->setHeaders($headers);
        }

        $response = $this->client->send($request);

        return $this->getResponse($response);
    }

    public function sendRequest(RequestInterface $request)
    {
        $body = ($request->getBody()) ? $request->getBody() : null;
        $httpRequest = $this->client->createRequest($request->getMethod(), $request->getUrl(), array('body' => $body));
        $httpRequest->setHeaders($request->getHeaders());

        $this->logger->log(
            'debug',
            sprintf('Sending http request to %s', $request->getUrl()),
            array('body' => (string) $request->getBody())
        );

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
}
