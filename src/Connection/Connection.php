<?php

/**
 * This file is part of the GraphAware Neo4j Client package.
 *
 * (c) GraphAware Limited <http://graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\Connection;

use GraphAware\Common\Driver\DriverInterface;

class Connection
{
    /**
     * @var string The Connection Alias
     */
    private $alias;

    /**
     * @var \GraphAware\Common\Driver\DriverInterface
     */
    private $driver;

    /**
     * Connection constructor.
     * @param $alias
     * @param \GraphAware\Common\Driver\DriverInterface $driver
     */
    public function __construct($alias, DriverInterface $driver)
    {
        $this->alias = (string) $alias;
        $this->driver = $driver;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return \GraphAware\Common\Driver\DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
