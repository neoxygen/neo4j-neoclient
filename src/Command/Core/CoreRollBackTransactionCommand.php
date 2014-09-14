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

namespace Neoxygen\NeoClient\Command\Core;

use Neoxygen\NeoClient\Command\AbstractCommand;
use Neoxygen\NeoClient\Request\Request;

class CoreRollBackTransactionCommand extends AbstractCommand
{
    private $transactionId;

    public function execute()
    {
        $request = new Request('DELETE', $this->getPath());

        return $this->httpClient->sendRequest($request);
    }

    public function getPath()
    {
        return $this->getBaseUrl() . '/db/data/transaction/'.$this->getTransactionId();
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