<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Request;

use Neoxygen\NeoClient\Connection\Connection;

class Request implements RequestInterface
{
    private $method;

    private $path;

    private $url;

    private $body;

    private $queryStrings;

    private $headers = [];

    private $options;

    private $connection;

    private $timeout;

    private $authMode;

    private $user;

    private $password;

    private $stream;

    private $queryMode;

    /**
     * @return mixed
     */
    public function getQueryMode()
    {
        return $this->queryMode;
    }

    /**
     * @param mixed $queryMode
     */
    public function setQueryMode($queryMode)
    {
        $this->queryMode = $queryMode;
    }

    public function hasQueryMode()
    {
        return null !== $this->queryMode;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param mixed $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return mixed
     */
    public function getAuthMode()
    {
        return $this->authMode;
    }

    /**
     * @param mixed $authMode
     */
    public function setAuthMode($authMode)
    {
        $this->authMode = $authMode;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param mixed $stream
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * @return mixed
     */
    public function getQueryStrings()
    {
        return $this->queryStrings;
    }

    /**
     * @param mixed $queryStrings
     */
    public function setQueryStrings($queryStrings)
    {
        $this->queryStrings = $queryStrings;
    }

    public function hasBody()
    {
        return null !== $this->body;
    }

    public function isStream()
    {
        return null !== $this->stream;
    }

    public function hasQueryStrings()
    {
        return null !== $this->queryStrings;
    }

    public function isSecured()
    {
        return null !== $this->authMode;
    }

    public function setInfoFromConnection(Connection $connection)
    {
        $this->connection = $connection->getAlias();
        if ($connection->isAuth()) {
            $this->authMode = true;
            $this->user = $connection->getAuthUser();
            $this->password = $connection->getAuthPassword();
        }
        $this->url = $connection->getBaseUrl().$this->path;

        return $this;
    }
}
