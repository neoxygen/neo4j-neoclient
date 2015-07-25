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

class CorePushToTransactionCommand extends AbstractCommand
{
    const METHOD = 'POST';

    const PATH = '/db/data/transaction/';

    public $query;

    public $parameters;

    public $resultDataContents;

    public $transactionId;

    protected $queryMode;

    public function setArguments($transactionId, $query, array $parameters = array(), array $resultDataContents = array(), $queryMode)
    {
        $this->transactionId = (int) $transactionId;
        $this->query = $query;
        $this->parameters = $parameters;
        $this->resultDataContents = $resultDataContents;
        $this->queryMode = $queryMode;

        return $this;
    }

    public function execute()
    {
        return $this->process(self::METHOD, $this->getPath(), $this->prepareBody(), $this->connection, null, $this->queryMode);
    }

    public function prepareBody()
    {
        $statement = array();
        $statement['statement'] = $this->query;
        if (!empty($this->parameters)) {
            $statement['parameters'] = $this->parameters;
        }
        $statement['resultDataContents'] = $this->resultDataContents;
        $body = array(
            'statements' => array(
                $statement,
            ),
        );

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
