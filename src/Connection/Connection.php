<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Client\Connection;

use GraphAware\Bolt\GraphDatabase as BoltGraphDB;
use GraphAware\Neo4j\Client\Exception\Neo4jException;
use GraphAware\Bolt\Exception\MessageFailureException;
use GraphAware\Neo4j\Client\HttpDriver\GraphDatabase as HttpGraphDB;

class Connection
{
    /**
     * @var string The Connection Alias
     */
    private $alias;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var \GraphAware\Common\Driver\DriverInterface The configured driver
     */
    private $driver;

    /**
     * Connection constructor.
     *
     * @param string $alias
     * @param string $uri
     */
    public function __construct($alias, $uri)
    {
        $this->alias = (string) $alias;
        $this->uri = (string) $uri;
        $this->buildDriver();
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    private function buildDriver()
    {
        if (preg_match('/bolt/', $this->uri)) {
            $this->driver = BoltGraphDB::driver($this->uri);
        } elseif (preg_match('/http/', $this->uri)) {
            $this->driver = HttpGraphDB::driver($this->uri);
        } else {
            throw new \RuntimeException(sprintf('Unable to build a driver from uri "%s"', $this->uri));
        }
    }

    /**
     * @return \GraphAware\Common\Driver\DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    public function createPipeline($query, $parameters, $tag)
    {
        $session = $this->driver->session();
        $parameters = is_array($parameters) ? $parameters : array();

        return $session->createPipeline($query, $parameters, $tag);
    }

    public function run($statement, $parameters, $tag)
    {
        $parameters = is_array($parameters) ? $parameters : array();
        $session = $this->driver->session();
        try {
            $results = $session->run($statement, $parameters, $tag);

            return $results;
        } catch (MessageFailureException $e) {
            $exception = new Neo4jException($e->getMessage());
            $exception->setNeo4jStatusCode($e->getStatusCode());

            throw $exception;
        }
    }
}
