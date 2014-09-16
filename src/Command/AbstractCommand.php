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

namespace Neoxygen\NeoClient\Command;

use Neoxygen\NeoClient\Connection\Connection,
    Neoxygen\NeoClient\HttpClient\HttpClientInterface,
    Neoxygen\NeoClient\Request\Request;

abstract class AbstractCommand implements CommandInterface
{
    protected $connection;

    protected $httpClient;

    protected $request;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getBaseUrl()
    {
        return $this->connection->getBaseUrl();
    }

    public function createRequest()
    {
        $request = new Request();
        if ($this->connection->isAuth()) {
            $pwd = base64_encode($this->connection->getAuthUser().':'.$this->connection->getAuthPassword());
            $request->setHeaders(array(
                'Authorization' => 'Basic '.$pwd
            ));
        }

        $this->request = $request;

        return $request;
    }
}
