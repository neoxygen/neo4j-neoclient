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

class GuzzleHttpClient implements HttpClientInterface
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function send($method, $url, $body = null, array $headers = array())
    {
        $request = $this->client->createRequest($method, $url, array('body' => $body));

        if (!empty($headers)) {
            $request->setHeaders($headers);
        }

        return $this->client->send($request)->json();
    }

    public function sendRequest(RequestInterface $request)
    {
        $body = !empty($request->getBody()) ? $request->getBody() : null;
        $httpRequest = $this->client->createRequest($request->getMethod(), $request->getUrl(), array('body' => $body));
        $httpRequest->setHeaders($request->getHeaders());

        return $this->client->send($httpRequest)->json();

    }
}
