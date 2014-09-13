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

namespace Neoxygen\NeoClient\Connection;

use Neoxygen\NeoClient\Connection\Connection,
    Neoxygen\NeoClient\Exception\InvalidConnectionException;

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

    /**
     * Initialize connections array
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
     *
     * Register a new Collection
     *
     * @param Connection $connection
     */
    public function registerConnection(Connection $connection)
    {
        $this->connections[$connection->getAlias()] = $connection;
    }

    /**
     * @param  string|null                              $alias The connection's alias
     * @return Neoxygen\NeoClient\Connection\Connection The requested connection
     * @throws InvalidConnectionException               When the connection does not exist
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
     * @return The                        default Connection if defined, the first connection in the connections array otherwise
     * @throws InvalidConnectionException if no connections are configured
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
     * @param  string $alias The connection's alias to verify the existence
     * @return bool
     */
    public function hasConnection($alias)
    {
        return array_key_exists($alias, $this->connections);
    }
}
