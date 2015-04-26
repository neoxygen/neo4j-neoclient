<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Command;

use Neoxygen\NeoClient\HttpClient\HttpClientInterface;
use Neoxygen\NeoClient\Request\RequestBuilder;

abstract class AbstractCommand implements CommandInterface
{
    protected $connection;

    protected $httpClient;

    protected $requestBuilder;

    public function __construct(HttpClientInterface $httpClient, RequestBuilder $requestBuilder)
    {
        $this->httpClient = $httpClient;
        $this->requestBuilder = $requestBuilder;
    }

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    protected function process($method, $path, $body = null, $conn = null, $queryStrings = null, $queryMode = null, array $headers = array())
    {
        $request = $this->requestBuilder->buildRequest($method, $path, $body, $queryStrings, $conn, $queryMode, $headers);

        return $this->httpClient->sendRequest($request);
    }
}
