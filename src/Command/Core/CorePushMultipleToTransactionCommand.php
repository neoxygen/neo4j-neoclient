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

class CorePushMultipleToTransactionCommand extends AbstractCommand
{
    const METHOD = 'POST';

    const PATH = '/db/data/transaction/';

    public $statements;

    public $resultDataContents;

    public $transactionId;

    public function setArguments($transactionId, array $statements, array $resultDataContents = array())
    {
        $this->transactionId = (int) $transactionId;
        $this->statements = $statements;
        $this->resultDataContents = $resultDataContents;

        return $this;
    }

    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), $this->prepareBody(), $this->connection);
    }

    public function prepareBody()
    {
        $body = [];
        foreach ($this->statements as $statement) {
            $st = [];
            $k = isset($statement['query']) ? 'query' : 'statement';
            $st['statement'] = $statement[$k];
            if (isset($statement['params'])) {
                $st['parameters'] = $statement['params'];
            }
            $st['resultDataContents'] = $this->resultDataContents;
            $body['statements'][] = $st;
        }

        return json_encode($body);
    }

    public function getPath()
    {
        return self::PATH.$this->getTransactionId();
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
