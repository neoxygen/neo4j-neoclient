<?php

/**
 * This file is part of the Neoclient package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT License
 */

namespace Neoxygen\NeoClient\Transaction;

use Neoxygen\NeoClient\Exception\CommandException;
use Neoxygen\NeoClient\Client;

class PreparedTransaction
{
    /**
     * @var array
     */
    protected $statements = [];

    /**
     * @var null|string
     */
    protected $connection;

    /**
     * @var bool
     */
    protected $committed;

    /**
     * @var string Transaction Query Mode can be WRITE or READ
     */
    protected $queryMode;

    /**
     * @param null|string $connection Connection alias
     * @param null|string $queryMode  Transaction query Mode, can be READ or WRITE, default to WRITE
     */
    public function __construct($connection = null, $queryMode = Client::NEOCLIENT_QUERY_MODE_WRITE)
    {
        if (null !== $connection) {
            $this->connection = (string) $connection;
        }

        if ($queryMode !== Client::NEOCLIENT_QUERY_MODE_WRITE && $queryMode !== Client::NEOCLIENT_QUERY_MODE_READ) {
            throw new \InvalidArgumentExceptiont(sprintf(
                'The query mode %s for the PreparedTransaction is not valid',
                $queryMode
            ));
        }
        $this->queryMode = $queryMode;
        $this->committed = false;

        return $this;
    }

    /**
     * @param $q
     * @param null|array $p
     *
     * @return $this
     */
    public function pushQuery($q, $p = array())
    {
        if (!is_array($p)) {
            throw new CommandException('Cypher query parameters should be of type array or null');
        }
        $this->statements[] = array(
            'statement' => $q,
            'parameters' => $p,
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function hasStatements()
    {
        if (!empty($this->statements)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getQueryMode()
    {
        return $this->queryMode;
    }

    /**
     * Commit the prepared transaction.
     *
     * @return mixed
     *
     * @throws \Neoxygen\NeoClient\Exception\CommandException if the transaction was already committed
     */
    public function commit()
    {
        if ($this->committed) {
            throw new CommandException('The prepared transaction has already been commited');
        }

        $response = Client::commitPreparedTransaction($this);
        $this->committed = true;

        return $response;
    }
}
