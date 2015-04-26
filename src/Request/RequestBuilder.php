<?php

namespace Neoxygen\NeoClient\Request;

use Neoxygen\NeoClient\Connection\ConnectionManager;

class RequestBuilder
{
    protected $connectionManager;

    public function __construct(ConnectionManager $connectionManager)
    {
        $this->connectionManager = $connectionManager;
    }

    /**
     * @param $method
     * @param $path
     * @param null $body
     * @param null $queryStrings
     * @param null $conn
     *
     * @return Request
     */
    public function buildRequest($method, $path, $body = null, $queryStrings = null, $conn = null, $queryMode = null, array $headers = array())
    {
        $request = new Request();
        $connection = $this->connectionManager->getConnection($conn);
        $request->setMethod(strtoupper($method));
        $request->setPath($path);
        $request->setBody($body);
        $request->setQueryStrings($queryStrings);
        $request->setConnection($connection->getAlias());
        $request->setQueryMode($queryMode);
        $request->setHeaders($headers);
        if ($connection->isAuth()) {
            $request->setAuthMode(true);
            $request->setUser($connection->getAuthUser());
            $request->setPassword($connection->getAuthPassword());
        }
        $url = $connection->getBaseUrl().$path;
        $request->setUrl($url);

        return $request;
    }
}
