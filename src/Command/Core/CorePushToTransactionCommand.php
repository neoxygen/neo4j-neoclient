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

class CorePushToTransactionCommand extends AbstractCommand
{
    public $query;

    public $parameters;

    public $resultDataContents;

    public $transactionId;

    public function setArguments($transactionId, $query, array $parameters = array(), array $resultDataContents = array())
    {
        $this->transactionId = (int) $transactionId;
        $this->query = $query;
        $this->parameters = $parameters;
        $this->resultDataContents = $resultDataContents;

        return $this;
    }

    public function execute()
    {
        $request = $this->createRequest();
        $request->setMethod('POST');
        $request->setUrl($this->getPath());
        $request->setBody($this->prepareBody());

        return $this->httpClient->sendRequest($request);
    }

    public function prepareBody()
    {
        $statement = array();
        $statement['statement'] = $this->query;
        if (!empty($this->parameters)) {
            $statement['parameters'] = $this->parameters;
        }
        $body = array(
            'statements' => array(
                $statement
            )
        );

        return json_encode($body);
    }

    public function getPath()
    {
        return $this->getBaseUrl() . '/db/data/transaction/' . $this->getTransactionId() . '/commit';
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
