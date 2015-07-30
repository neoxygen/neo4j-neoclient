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

class Connection
{
    /**
     * @var string The Connection Alias
     */
    private $alias;

    /**
     * @var string The scheme to use for the Connection, could be http|https
     */
    private $scheme;

    /**
     * @var string The connection's host
     */
    private $host;

    /**
     * @var int The connection's port
     */
    private $port;

    /**
     * @var bool Whether or not to use Auth headers
     */
    private $authMode;

    /**
     * @var string The username for the authentication
     */
    private $authUser;

    /**
     * @var string The user's password for the authentication
     */
    private $authPassword;

    /**
     * @param $alias
     * @param string $scheme
     * @param string $host
     * @param int    $port
     * @param string $authUser     The user login when using the authentication extension
     * @param string $authPassword The user password when using the authentication extension
     */
    public function __construct(
        $alias,
        $scheme = 'http',
        $host = 'localhost',
        $port = 7474,
        $authMode = false,
        $authUser = null,
        $authPassword = null)
    {
        $this->alias = $alias;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->authMode = (bool) $authMode;
        $this->authUser = $authUser;
        $this->authPassword = $authPassword;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return string http|https
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->scheme.'://'.$this->host.':'.$this->port;
    }

    /**
     * @return bool
     */
    public function isAuth()
    {
        return $this->authMode;
    }

    /**
     * Sets the authentication mode to true.
     */
    public function setAuthMode()
    {
        $this->authMode = true;
    }

    /**
     * @return string
     */
    public function getAuthUser()
    {
        return $this->authUser;
    }

    /**
     * @param string $username
     */
    public function setAuthUser($username)
    {
        $this->authUser = $username;
    }

    /**
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->authPassword;
    }

    /**
     * @param string $password
     */
    public function setAuthPassword($password)
    {
        $this->authPassword = $password;
    }
}
