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
     * @param null|string $connection Connection alias
     */
    public function __construct($connection = null)
    {
        if (null !== $connection) {
            $this->connection = (string) $connection;
        }
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
