<?php

/*
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\HttpDriver;

use GraphAware\Common\Driver\ConfigInterface;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;

class Configuration implements ConfigInterface
{
    /**
     * @var int
     * @deprecated Will be removed in 5.0
     */
    protected $timeout;

    /**
     * @var string
     * @deprecated Will be removed in 5.0
     */
    protected $curlInterface;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @return Configuration
     */
    public static function create(HttpClient $httpClient = null, RequestFactory $requestFactory = null)
    {
        $config = new self();
        $config->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $config->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();

        return $config;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param HttpClient $httpClient
     *
     * @return Configuration
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $new = clone $this;
        $new->httpClient = $httpClient;

        return $new;
    }

    /**
     * @return RequestFactory
     */
    public function getRequestFactory()
    {
        return $this->requestFactory;
    }

    /**
     * @param RequestFactory $requestFactory
     *
     * @return Configuration
     */
    public function setRequestFactory(RequestFactory $requestFactory)
    {
        $new = clone $this;
        $new->requestFactory = $requestFactory;

        return $new;
    }

    /**
     * @param int $timeout
     *
     * @return Configuration
     * @deprecated Will be removed in 5.0
     */
    public function withTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param string $interface
     *
     * @return $this
     * @deprecated Will be removed in 5.0
     */
    public function withCurlInterface($interface)
    {
        $this->curlInterface = $interface;

        return $this;
    }

    /**
     * @return int
     * @deprecated Will be removed in 5.0
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return string
     * @deprecated Will be removed in 5.0
     */
    public function getCurlInterface()
    {
        return $this->curlInterface;
    }
}
