<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Connection;

use Neoxygen\NeoClient\Exception\InvalidConnectionException;
use Neoxygen\NeoClient\Exception\HttpException;

class ConnectionManager
{
    /**
     * @var array Array of all registered connections
     */
    private $connections;

    /**
     * @var string The alias of the default connection
     */
    private $defaultConnection;

    private $master;

    private $slaves = [];

    /**
     * Initialize connections array.
     */
    public function __construct()
    {
        $this->connections = array();
    }

    /**
     * @return array An array of the registered connections with the form 'alias' => Connection Object
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Register a new Collection.
     *
     * @param Connection $connection
     */
    public function registerConnection(Connection $connection)
    {
        $this->connections[$connection->getAlias()] = $connection;
    }

    /**
     * @param string|null $alias The connection's alias
     *
     * @return \Neoxygen\NeoClient\Connection\Connection The requested connection
     *
     * @throws InvalidConnectionException When the connection does not exist
     */
    public function getConnection($alias = null)
    {
        $message = null;

        if (null === $alias && empty($this->connections)) {
            $message = sprintf('There is no connection configured');
        } elseif (null !== $alias && !array_key_exists($alias, $this->connections)) {
            $message = sprintf('The connection with alias "%s" is not configured', $alias);
        }
        if ($message) {
            throw new InvalidConnectionException($message);
        }

        if (null === $alias) {
            return $this->getDefaultConnection();
        }

        return $this->connections[$alias];
    }

    /**
     * @return \Neoxygen\NeoClient\Connection\Connection The default Connection if defined, the first connection in the connections array otherwise
     *
     * @throws \Neoxygen\NeoClient\Exception\InvalidConnectionException If no connections are configured
     */
    public function getDefaultConnection()
    {
        if (!$this->defaultConnection && empty($this->connections)) {
            throw new InvalidConnectionException('There are no connections configured');
        }

        if (!$this->defaultConnection) {
            reset($this->connections);

            return current($this->connections);
        }

        return $this->getConnection($this->defaultConnection);
    }

    /**
     * @param string $alias The alias of the connection to be set as default
     */
    public function setDefaultConnection($alias)
    {
        if (!array_key_exists($alias, $this->connections)) {
            throw new InvalidConnectionException(sprintf('The connection "%s" is not configured', $alias));
        }

        $this->defaultConnection = $alias;
    }

    /**
     * Returns whether or not a connection exist for a given alias.
     *
     * @param string $alias The connection's alias to verify the existence
     *
     * @return bool
     */
    public function hasConnection($alias)
    {
        return array_key_exists($alias, $this->connections);
    }

    public function setMasterConnection($connectionAlias)
    {
        if (!array_key_exists($connectionAlias, $this->connections)) {
            throw new InvalidConnectionException(sprintf('The connection "%s" is not configured', $alias));
        }

        $this->master = $connectionAlias;
    }

    public function setSlaveConnections(array $slaves)
    {
        foreach ($slaves as $connectionAlias) {
            if (!array_key_exists($connectionAlias, $this->connections)) {
                throw new InvalidConnectionException(sprintf('The connection "%s" is not configured', $alias));
            }
            $this->slaves[] = $connectionAlias;
        }
    }

    public function getWriteConnection()
    {
        if (null !== $this->master) {
            return $this->getMasterConnection();
        }

        return $this->getConnection();
    }

    public function getReadConnection()
    {
        if (null !== $this->master && !empty($this->slaves)) {
            return $this->getConnection(current($this->slaves));
        }

        return $this->getConnection();
    }

    public function getMasterConnection()
    {
        if (null !== $this->master) {
            return $this->getConnection($this->master);
        }

        return;
    }

    public function isHA()
    {
        if (null !== $this->master && !empty($this->slaves)) {
            return true;
        }

        return false;
    }

    public function hasNextSlave(array $usedSlaves)
    {
        if (count($this->slaves) > count($usedSlaves)) {
            return true;
        }

        return false;
    }

    public function getNextSlave(array $usedSlaves)
    {
        foreach ($this->slaves as $slave) {
            if (!in_array($slave, $usedSlaves)) {
                return $slave;
            }
        }

        throw new HttpException('There are no more slaves to process');
    }

    public function getSlaves()
    {
        return $this->slaves;
    }

    public function getHAConfig()
    {
        if (null !== $this->master && !empty($this->slaves)) {
            return array(
                'master' => $this->master,
                'slaves' => $this->slaves,
            );
        }

        return;
    }

    public function getConnectionAliases()
    {
        $aliases = [];
        foreach ($this->connections as $k => $c) {
            $aliases[$k] = $k;
        }

        return $aliases;
    }

    public function getMasterConnectionAlias()
    {
        if ($this->isHA()) {
            return $this->getMasterConnection()->getAlias();
        }

        return;
    }
}
