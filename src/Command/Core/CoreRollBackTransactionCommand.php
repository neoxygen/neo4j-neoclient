<?php

/**
 * This file is part of the "-[:NEOXYGEN]->" NeoClient package.
 *
 * (c) Neoxygen.io <http://neoxygen.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neoxygen\NeoClient\Command\Core;

use Neoxygen\NeoClient\Command\AbstractCommand;

class CoreRollBackTransactionCommand extends AbstractCommand
{
    const METHOD = 'DELETE';

    const PATH = '/db/data/transaction/';

    private $transactionId;

    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), null, $this->connection);
    }

    public function getPath()
    {
        return self::PATH.$this->getTransactionId();
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function setTransactionId($id)
    {
        $this->transactionId = $id;

        return $this;
    }
}
